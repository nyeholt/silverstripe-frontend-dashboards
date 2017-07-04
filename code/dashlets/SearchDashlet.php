<?php

/**
 * Search listing.
 *
 * @author Nathan Glasl <nathan@symbiote.com.au>
 */

class SearchDashlet extends Dashlet {
	public static $title = "Search";
	public static $cmsTitle = "Search";
	public static $description = "Simple search mechanism";
}

class SearchDashlet_Controller extends Dashlet_Controller {

	private static $allowed_actions = array(
		'SearchForm',
		'results'
	);

    public $stopwords = array(
		'street' => 1, 'st' => 1, 'lane' => 1, 'ln' => 1, 'road' => 1, 'rd' => 1,
		"a" => 1, "about" => 1, "above" => 1, "above" => 1, "across" => 1,
		"after" => 1, "afterwards" => 1, "again" => 1, "against" => 1,
		"all" => 1, "almost" => 1, "alone" => 1, "along" => 1,
		"already" => 1, "also" => 1,"although" => 1,"always" => 1,"am" => 1,
		"among" => 1, "amongst" => 1, "amoungst" => 1, "amount" => 1,
		"an" => 1, "and" => 1, "another" => 1, "any" => 1,"anyhow" => 1,
		"anyone" => 1,"anything" => 1,"anyway" => 1, "anywhere" => 1,
		"are" => 1, "around" => 1, "as" => 1,  "at" => 1, "back" => 1,
		"be" => 1,"became" => 1, "because" => 1,"become" => 1,
		"becomes" => 1, "becoming" => 1, "been" => 1, "before" => 1,
		"beforehand" => 1, "behind" => 1, "being" => 1, "below" => 1,
		"beside" => 1, "besides" => 1, "between" => 1, "beyond" => 1,
		"both" => 1, "bottom" => 1,"but" => 1, "by" => 1, "call" => 1,
		"cannot" => 1, "cant" => 1, "co" => 1, "con" => 1, "could" => 1, "couldnt" => 1, "cry" => 1, "de" => 1, "describe" => 1, "detail" => 1, "do" => 1, "done" => 1, "down" => 1, "due" => 1, "during" => 1, "each" => 1, "eg" => 1, "eight" => 1, "either" => 1, "eleven" => 1,"else" => 1, "elsewhere" => 1, "empty" => 1, "enough" => 1, "etc" => 1, "even" => 1, "ever" => 1, "every" => 1, "everyone" => 1, "everything" => 1, "everywhere" => 1, "except" => 1, "few" => 1, "fifteen" => 1, "fify" => 1, "fill" => 1, "find" => 1, "fire" => 1, "first" => 1, "five" => 1, "for" => 1, "former" => 1, "formerly" => 1, "forty" => 1, "found" => 1, "four" => 1, "from" => 1, "front" => 1, "full" => 1, "further" => 1, "get" => 1, "give" => 1, "go" => 1, "had" => 1, "has" => 1, "hasnt" => 1, "have" => 1, "he" => 1, "hence" => 1, "her" => 1, "here" => 1, "hereafter" => 1, "hereby" => 1, "herein" => 1, "hereupon" => 1, "hers" => 1, "herself" => 1, "him" => 1, "himself" => 1, "his" => 1, "how" => 1, "however" => 1, "hundred" => 1, "ie" => 1, "if" => 1, "in" => 1, "inc" => 1, "indeed" => 1, "interest" => 1, "into" => 1, "is" => 1, "it" => 1, "its" => 1, "itself" => 1, "keep" => 1, "last" => 1, "latter" => 1, "latterly" => 1, "least" => 1, "less" => 1, "ltd" => 1, "made" => 1, "many" => 1, "may" => 1, "me" => 1, "meanwhile" => 1, "might" => 1, "mill" => 1, "mine" => 1, "more" => 1, "moreover" => 1, "most" => 1, "mostly" => 1, "move" => 1, "much" => 1, "must" => 1, "my" => 1, "myself" => 1, "name" => 1, "namely" => 1, "neither" => 1, "never" => 1, "nevertheless" => 1, "next" => 1, "nine" => 1, "no" => 1, "nobody" => 1, "none" => 1, "noone" => 1, "nor" => 1, "not" => 1, "nothing" => 1, "now" => 1, "nowhere" => 1, "of" => 1, "off" => 1, "often" => 1, "on" => 1, "once" => 1, "only" => 1, "onto" => 1, "or" => 1, "other" => 1, "others" => 1, "otherwise" => 1, "our" => 1, "ours" => 1, "ourselves" => 1, "out" => 1, "over" => 1, "own" => 1,"part" => 1, "per" => 1, "perhaps" => 1, "please" => 1, "put" => 1, "rather" => 1, "re" => 1, "same" => 1, "see" => 1, "seem" => 1, "seemed" => 1, "seeming" => 1, "seems" => 1, "serious" => 1, "several" => 1, "she" => 1, "should" => 1, "show" => 1, "side" => 1, "since" => 1, "sincere" => 1, "six" => 1, "sixty" => 1, "so" => 1, "some" => 1, "somehow" => 1, "someone" => 1, "something" => 1, "sometime" => 1, "sometimes" => 1, "somewhere" => 1, "still" => 1, "such" => 1, "system" => 1, "take" => 1, "ten" => 1, "than" => 1, "that" => 1, "the" => 1, "their" => 1, "them" => 1, "themselves" => 1, "then" => 1, "thence" => 1, "there" => 1, "thereafter" => 1, "thereby" => 1, "therefore" => 1, "therein" => 1, "thereupon" => 1, "these" => 1, "they" => 1, "thickv" => 1, "thin" => 1, "third" => 1, "this" => 1, "those" => 1, "though" => 1, "three" => 1, "through" => 1, "throughout" => 1, "thru" => 1, "thus" => 1, "to" => 1, "together" => 1, "too" => 1, "top" => 1, "toward" => 1, "towards" => 1, "twelve" => 1, "twenty" => 1, "two" => 1, "un" => 1, "under" => 1, "until" => 1, "up" => 1, "upon" => 1, "us" => 1, "very" => 1, "via" => 1, "was" => 1, "we" => 1, "well" => 1, "were" => 1, "what" => 1, "whatever" => 1, "when" => 1, "whence" => 1, "whenever" => 1, "where" => 1, "whereafter" => 1, "whereas" => 1, "whereby" => 1, "wherein" => 1, "whereupon" => 1, "wherever" => 1, "whether" => 1, "which" => 1, "while" => 1, "whither" => 1, "who" => 1, "whoever" => 1, "whole" => 1, "whom" => 1, "whose" => 1, "why" => 1, "with" => 1, "within" => 1, "without" => 1, "would" => 1, "yet" => 1, "you" => 1, "your" => 1, "yours" => 1, "yourself" => 1, "yourselves" => 1, "the"
    );


	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(BA_SIS_COMMUNITY_PATH . '/javascript/dashlet-searchdashlet.js');
	}

	public function SearchForm() {

		// Use the extensible search form, since it's a dependency of silverstripe-seed.

		$form = null;
		
        $fields = FieldList::create([
            TextField::create('Terms', 'Terms')->setAttribute('placeholder', 'Search terms')
        ]);
        $actions = FieldList::create([
            FormAction::create('results', 'Go')
        ]);
        $form = Form::create($this, 'SearchForm', $fields, $actions)
            ->setFormMethod('GET')
            ->addExtraClass('search-dashlet-form');

		return $form;
	}

	public function results($data, $form) {
        $results = [];
        if (!strlen($data['Terms'])) {
            return ArrayList::create();
        }

        $keywords = $this->buildKeywords($data['Terms']);
        
        if ($form instanceof SearchForm) {
            $query = $form->getSearchQuery();
            $results = $form->getResults(5);
        } else {

            $results = SiteTree::get()->filterAny([
                'Title:PartialMatch' => $keywords['all'],
                'Content:PartialMatch' => $keywords['all']
            ])->sort('LastEdited DESC')->limit(200);
        }

        $collection = ArrayList::create();
		foreach ($results as $item) {
			$item->URL = $item->hasMethod('Link') ? $item->Link() : $item->URLSegment;
            $item->SearchScore = $this->scoreResult($item, $keywords);
            $collection->push($item);
		}

        $collection = $collection->sort('SearchScore DESC');

		// We require an associative array at the top level, so we'll create this and insert our search results.

        $finalSet = [];
        foreach ($collection as $res) {
            $finalSet[] = [
                'Title' => $res->Title,
                'Link'  => $res->Link(),
                'Score' => $res->SearchScore,
                'Summary' => '',
            ];
        }

		$data = array('Keywords' => $keywords, 'Query' => array($data['Terms']), 'Results' => ArrayList::create($finalSet));

        return $this->customise($data)->renderWith('SearchDashlet_results');
		// Convert this list to json so we can interate through using javascript.

		return Convert::array2json($data);
    }

    public function scoreResult($item, $keywords) {
        $text = strtolower($item->Title . ' ' . $item->Content);

        foreach ($keywords['not'] as $notWor) {
            if (strpos($text, $notWor) !== false) {
                return 0;
            }
        }

        foreach ($keywords['and'] as $andWord) {
            if (strpos($text, $andWord) === false) {
                return 0;
            }
        }

        $total = 0;

        foreach ($keywords['all'] as $word) {
            $total += substr_count($text, $word);

            $inTitle = stripos($item->Title, $word);
            if ($inTitle !== false) {
                if ($inTitle === 0) {
                    $total *= 5;
                } else {
                    $total *= 2;
                }
            }
        }

        return $total;
    }

    protected function buildKeywords($keywords) {
        
        $andProcessor = create_function('$matches','
	 		return " +" . $matches[2] . " +" . $matches[4] . " ";
	 	');
	 	$notProcessor = create_function('$matches', '
	 		return " -" . $matches[3];
	 	');

	 	$keywords = preg_replace_callback('/()("[^()"]+")( and )("[^"()]+")()/i', $andProcessor, $keywords);
	 	$keywords = preg_replace_callback('/(^| )([^() ]+)( and )([^ ()]+)( |$)/i', $andProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )("[^"()]+")/i', $notProcessor, $keywords);
		$keywords = preg_replace_callback('/(^| )(not )([^() ]+)( |$)/i', $notProcessor, $keywords);

        $andTerms = [];
        $orTerms = [];
        $notTerms = [];
        $all = [];

        $terms = explode(' ', $keywords);

        foreach ($terms as $term) {
            if (strlen($term) < 3) {
                continue;
            }
            $term = strtolower($term);
            $isNeg = $isPlus = false;

            if ($term{0} === '+') {
                $term = substr($term, 1);
                $isPlus = true;
            }
            if ($term{0} === '-') {
                $term = substr($term, 1);
                $isNeg = true;
            }

            if (isset($this->stopwords[$term])) {
                continue;
            }

            if ($isNeg) {
                $notTerms[] = $term;
            } else if ($isPlus) {
                $andTerms[] = $term;
                $all[] = $term;
            } else {
                $orTerms[] = $term;
                $all[] = $term;
            }
        }
        
        return ['all' => $all, 'and' => $andTerms, 'not' => $notTerms, 'or' => $orTerms];
    }
}