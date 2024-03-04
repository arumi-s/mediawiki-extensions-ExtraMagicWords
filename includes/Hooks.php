<?php

namespace MediaWiki\Extension\ExtraMagicWords;

use Config;
use Parser;
use PPFrame;
use Html;
use MediaWiki\Page\WikiPageFactory;

class Hooks implements
	\MediaWiki\Hook\ParserFirstCallInitHook,
	\MediaWiki\Hook\GetMagicVariableIDsHook,
	\Mediawiki\Hook\ParserGetVariableValueSwitchHook
{
	private $fixedVariableValues = [
		'!!' => '||',
		'(' => '{',
		')' => '}',
		'((' => '{{',
		'))' => '}}',
		'opensb' => '[',
		'closesb' => ']',
		'opensb2' => '[[',
		'closesb2' => ']]',
		'.' => '·',
	];

	/** @var Config */
	private $config;

	/** @var WikiPageFactory */
	private $wikiPageFactory;

	/**
	 * @param Config $config
	 * @param WikiPageFactory $wikiPageFactory
	 */
	public function __construct(
		Config $config,
		WikiPageFactory $wikiPageFactory
	) {
		$this->config = $config;
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/**
	 * Registers our parser functions with a fresh parser.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 *
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit($parser)
	{
		$parser->setFunctionHook('pagenameh', [ExtraMagicWords::class, 'pagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('fullpagenameh', [ExtraMagicWords::class, 'fullpagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('rootpagenameh', [ExtraMagicWords::class, 'rootpagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('basepagenameh', [ExtraMagicWords::class, 'basepagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('subpagenameh', [ExtraMagicWords::class, 'subpagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('talkpagenameh', [ExtraMagicWords::class, 'talkpagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('subjectpagenameh', [ExtraMagicWords::class, 'subjectpagenameh'], SFH_NO_HASH);
		$parser->setFunctionHook('createuser', [ExtraMagicWords::class, 'createuser'], SFH_NO_HASH);
	}

	/**
	 * Modifies the list of magic variables.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GetMagicVariableIDs
	 *
	 * @param string[] $variableIDs
	 */
	public function onGetMagicVariableIDs(&$variableIDs)
	{
		foreach ($this->fixedVariableValues as $name => $value) {
			$variableIDs[] = $name;
		}
		$variableIDs[] = 'clearfix';

		$variableIDs[] = 'pagenameh';
		$variableIDs[] = 'fullpagenameh';
		$variableIDs[] = 'rootpagenameh';
		$variableIDs[] = 'basepagenameh';
		$variableIDs[] = 'subpagenameh';
		$variableIDs[] = 'talkpagenameh';
		$variableIDs[] = 'subjectpagenameh';

		$variableIDs[] = 'createuser';
	}

	/**
	 * Assigns values to magic variables.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserGetVariableValueSwitch
	 *
	 * @param Parser $parser
	 * @param string[] $variableCache
	 * @param string $magicWordId
	 * @param string $ret
	 * @param PPFrame $frame
	 */
	public function onParserGetVariableValueSwitch($parser, &$variableCache, $magicWordId, &$ret, $frame)
	{
		if (isset($this->fixedVariableValues[$magicWordId])) {
			$ret = $this->fixedVariableValues[$magicWordId];
			return true;
		}

		switch ($magicWordId) {
			case 'clearfix':
				$classname = $this->config->get("ExtraMagicWordsClearFixClass");
				$ret = Html::element('div', ['class' => $classname]);
				break;
			case 'createuser':
				$wikiPage = $this->wikiPageFactory->newFromTitle($parser->getTitle());

				if (!$wikiPage->exists()) {
					$ret = '';
				} else {
					$creator = $wikiPage->getCreator();
					$ret = $creator ? $creator->getName() : '';
				}
				break;
			case 'pagenameh':
				$ret = $parser->getTitle()->getText();
				break;
			case 'fullpagenameh':
				$ret = $parser->getTitle()->getPrefixedText();
				break;
			case 'subpagenameh':
				$ret = $parser->getTitle()->getSubpageText();
				break;
			case 'rootpagenameh':
				$ret = $parser->getTitle()->getRootText();
				break;
			case 'basepagenameh':
				$ret = $parser->getTitle()->getBaseText();
				break;
			case 'talkpagenameh':
				$title = $parser->getTitle();
				if ($title->canHaveTalkPage()) {
					$talkPage = $title->getTalkPage();
					$ret = $talkPage->getPrefixedText();
				} else {
					$ret = '';
				}
				break;
			case 'subjectpagenameh':
				$subjPage = $parser->getTitle()->getSubjectPage();
				$ret = $subjPage->getPrefixedText();
				break;
		}

		$variableCache[$magicWordId] = $ret;
		return true;
	}
}
