<?php

namespace MediaWiki\Extension\ExtraMagicWords;

use MediaWiki\MediaWikiServices;
use Parser;
use Title;

/**
 * Extra Magic Words handlers
 */
class ExtraMagicWords
{
	/**
	 * Functions to get the creator of a page
	 * @param Parser $parser
	 * @param string|null $title
	 * @return string
	 */
	public static function createuser(Parser $parser, $text = null)
	{
		$title = Title::newFromText($text);
		if ($title === null) {
			return '';
		}

		$wikiPageFactory = MediaWikiServices::getInstance()->getWikiPageFactory();
		$wikiPage = $wikiPageFactory->newFromTitle($title);

		if (!$wikiPage->exists()) {
			return '';
		}
		$creator = $wikiPage->getCreator();
		return $creator ? $creator->getName() : '';
	}

	/**
	 * Functions to get and normalize pagenames, corresponding to the magic words
	 * of the same names
	 * @param Parser $parser
	 * @param string|null $title
	 * @return string
	 */
	public static function pagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null) {
			return '';
		}
		return $t->getText();
	}

	public static function fullpagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null || !$t->canHaveTalkPage()) {
			return '';
		}
		return $t->getPrefixedText();
	}

	public static function subpagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null) {
			return '';
		}
		return $t->getSubpageText();
	}

	public static function rootpagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null) {
			return '';
		}
		return $t->getRootText();
	}

	public static function basepagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null) {
			return '';
		}
		return $t->getBaseText();
	}

	public static function talkpagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null || !$t->canHaveTalkPage()) {
			return '';
		}
		return $t->getTalkPage()->getPrefixedText();
	}

	public static function subjectpagenameh(Parser $parser, $title = null)
	{
		$t = Title::newFromText($title);
		if ($t === null) {
			return '';
		}
		return $t->getSubjectPage()->getPrefixedText();
	}
}
