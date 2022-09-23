<?php

$IP = dirname( dirname( dirname( __DIR__ ) ) );

require_once "$IP/maintenance/Maintenance.php";

use BlueSpice\Content\Entity as EntityContent;
use BlueSpice\Social\Entity;
use BlueSpice\Social\Entity\Text;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\SlotRecord;

class BSSocialMigrateStash extends LoggedUpdateMaintenance {

	/**
	 * @return bool
	 */
	protected function doDBUpdates(): bool {
		global $wgExtraNamespaces;
		$this->output( "...Update '{$this->getUpdateKey()}'\n" );
		// must be available even without configuration when updating
		if ( !defined( 'NS_SOCIALENTITY' ) ) {
			define( "NS_SOCIALENTITY", 1506 );
			$wgExtraNamespaces[NS_SOCIALENTITY] = 'SocialEntity';
		}
		if ( !defined( 'NS_SOCIALENTITY_TALK' ) ) {
			define( 'NS_SOCIALENTITY_TALK', 1507 );
			$wgExtraNamespaces[NS_SOCIALENTITY_TALK] = 'SocialEntity_talk';
		}

		$data = $this->getStashData();
		$count = count( $data );
		$this->output( "   Migrate Stash($count): " );
		if ( $count < 1 ) {
			$this->output( " OK\n" );
			return true;
		}
		$titles = $this->makeTitleRelations( $data );
		if ( count( $titles ) < 1 ) {
			$this->output( " OK\n" );
			return true;
		}
		foreach ( $titles as $id => $relation ) {
			$attachments = $this->extractAttachmentData(
				$relation['title'],
				$relation['stash']
			);
			$status = $this->ammendAttachments( $relation['title'], $attachments );
			if ( $status->isOK() ) {
				foreach ( $attachments as $attachment ) {
					$this->output( '.' );
				}
				continue;
			}
			foreach ( $attachments as $attachment ) {
				$this->output( 'f' );
			}
		}
		$this->output( " OK\n" );
		return true;
	}

	/**
	 * @return string
	 */
	protected function getUpdateKey(): string {
		return 'bs_social-stashmigration';
	}

	/**
	 * @param Title|null $title
	 * @return stdClass|null
	 */
	private function resolveNativeDataFromTitle( ?Title $title ): ?stdClass {
		if ( !$title || !$title->exists() ) {
			return null;
		}

		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		if ( !$wikiPage ) {
			return null;
		}
		$content = $wikiPage->getContent();
		if ( !$content ) {
			return null;
		}
		$text = ( $content instanceof TextContent ) ? $content->getText() : '';

		$content = new EntityContent( $text );
		$data = (object)$content->getData()->getValue();

		return $data;
	}

	/**
	 * @return stdClass[]
	 */
	private function getStashData(): array {
		$return = [];
		// must be available even without the search index when updating
		$res = $this->getDB( DB_PRIMARY )->select(
			'page',
			'page_id',
			[ 'page_namespace' => NS_SOCIALENTITY, 'page_content_model' => 'BSSocial' ],
			__METHOD__
		);
		$revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
		foreach ( $res as $row ) {
			$title = Title::newFromID( $row->page_id );
			$data = $this->resolveNativeDataFromTitle( $title );
			if ( !$data ) {
				continue;
			}
			if ( !isset( $data->{Entity::ATTR_TYPE} )
				|| $data->{Entity::ATTR_TYPE} !== 'stash' ) {
				continue;
			}
			if ( isset( $data->{Entity::ATTR_ARCHIVED} )
				&& $data->{Entity::ATTR_ARCHIVED} === true ) {
				$this->output( 's' );
				continue;
			}
			$firstRev = $revisionLookup->getFirstRevision( $title->toPageIdentity() );
			$data->{Entity::ATTR_TIMESTAMP_CREATED} = $firstRev->getTimestamp();
			$return[$data->{Entity::ATTR_ID}] = $data;
		}
		return $return;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	private function makeTitleRelations( $data ): array {
		$titles = [];
		foreach ( $data as $entityData ) {
			if ( empty( $entityData->wikipageid ) ) {
				$this->output( "f" );
				continue;
			}
			$title = Title::newFromID( $entityData->wikipageid );
			if ( !$title || !$title->exists() ) {
				$this->output( "s" );
				continue;
			}
			if ( !isset( $titles[(int)$title->getArticleID()] ) ) {
				$titles[(int)$title->getArticleID()] = [
					'title' => $title,
					'stash' => [],
				];
			}
			$titles[(int)$title->getArticleID()]['stash'][] = $entityData;
		}
		return $titles;
	}

	/**
	 * @param Title $title
	 * @param array $stash
	 * @return array
	 */
	private function extractAttachmentData( Title $title, array $stash ): array {
		$attachments = [];
		$services = MediaWikiServices::getInstance();
		$lang = $services->getContentLanguage();
		$userFactory = $services->getUserFactory();
		foreach ( $stash as $entry ) {
			if ( empty( $entry->{Text::ATTR_TEXT} ) ) {
				continue;
			}
			$output = $this->getParserOutput( $title, $entry->{Text::ATTR_TEXT} );
			if ( !$output ) {
				$this->output( 's' );
				continue;
			}
			$date = $time = $userLink = '';
			$user = $userFactory->newFromId( $entry->{Text::ATTR_OWNER_ID} );
			if ( $user && !$user->isAnon() ) {
				$userLink = $user->getUserPage()->getFullText();
			}
			if ( !empty( $entry->{Text::ATTR_TIMESTAMP_CREATED} ) ) {
				$date = $lang->date( $entry->{Text::ATTR_TIMESTAMP_CREATED} );
				$time = $lang->time( $entry->{Text::ATTR_TIMESTAMP_CREATED} );
			}
			foreach ( $output->getImages() as $text => $id ) {
				$data = [
					'name' => $text,
					'link' => Title::makeTitle( NS_MEDIA, $text )->getFullText(),
					'user' => $userLink,
					'date' => $date,
					'time' => $time,
				];
				$attachments[] = $data;
			}
			// curretnly links have been removed from supported attachments,
			// so we can ignore it
			// $output->getLinks()
		}
		return $attachments;
	}

	/**
	 * @param Title $title
	 * @param string $text
	 * @return ParserOutput|null
	 */
	private function getParserOutput( Title $title, string $text ): ?ParserOutput {
		$parser = new Parser();
		$return = null;
		try {
			$return = $parser->parse(
				html_entity_decode( $text ),
				$title,
				ParserOptions::newFromUser( $this->getUser() )
			);
		} catch ( Exception $e ) {
		}
		return $return;
	}

	/**
	 * @return User
	 */
	public function getUser(): User {
		return MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
			->getMaintenanceUser()->getUser();
	}

	/**
	 * @param Title $title
	 * @param array $attachments
	 * @return Status
	 */
	private function ammendAttachments( Title $title, array $attachments ): Status {
		$wikiPage = MediaWikiServices::getInstance()->getWikiPageFactory()->newFromTitle( $title );
		if ( !$wikiPage ) {
			return Status::newFatal( 'Invalid WikiPage' );
		}
		$content = $wikiPage->getContent();
		if ( !$content instanceof WikitextContent ) {
			return Status::newFatal( 'Not WikitextContent' );
		}
		$text = $this->ammendContent( $content->getText(), $attachments );
		return $this->savePage( $wikiPage, new WikitextContent( $text ) );
	}

	/**
	 * @param string $text
	 * @param array $attachments
	 * @return string
	 */
	private function ammendContent( string $text, array $attachments ): string {
		$msg = Message::newFromKey( 'bs-social-wikipage-attachments-section-heading' );
		$text .= "\n\n";
		$text .= "== {$msg->inContentLanguage()->plain()} ==\n";
		$text .= "<!--\n";
		foreach ( $attachments as $attachment ) {
			$text .= "* {$attachment['link']}|{$attachment['name']}";
			if ( !empty( $attachment['user'] ) ) {
				$text .= " -- {$attachment['user']}";
			}
			if ( !empty( $attachment['date'] ) ) {
				$text .= ", {$attachment['date']}, {$attachment['time']}";
			}
			$text .= "\n";
		}
		$text .= "-->\n";
		$text .= "<attachments>\n";
		foreach ( $attachments as $attachment ) {
			$text .= "* [[{$attachment['link']}]]\n";
		}
		$text .= "</attachments>\n";
		return $text;
	}

	/**
	 * @param WikiPage $wikiPage
	 * @param Content $content
	 * @return Status
	 */
	private function savePage( WikiPage $wikiPage, Content $content ): Status {
		$revision = null;
		try {
			$updater = $wikiPage->newPageUpdater( $this->getUser() );
			$updater->setContent( SlotRecord::MAIN, $content );
			$revision = $updater->saveRevision(
				CommentStoreComment::newUnsavedComment( 'Attachment migration' )
			);
		} catch ( Exception $e ) {
			return Status::newFatal( $e->getMessage() );
		}
		if ( !$revision ) {
			return Status::newFatal( "Save Failed for {$wikiPage->getTitle()->getFullText()}" );
		}
		return Status::newGood();
	}
}
