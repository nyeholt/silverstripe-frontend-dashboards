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

    private static $search_types = [
        'SiteTree',
    ];

    public $stopwords = array(
		'street' => 1, 'st' => 1, 'lane' => 1, 'ln' => 1, 'road' => 1, 'rd' => 1,
		"a" => 1, "above" => 1, "above" => 1, "across" => 1,
		"after" => 1, "afterwards" => 1, "again" => 1, "against" => 1,
		"all" => 1, "almost" => 1, "alone" => 1, "along" => 1,
		"already" => 1, "also" => 1,"always" => 1,"am" => 1,
		"an" => 1, "and" => 1, "any" => 1,"anyhow" => 1,
		"are" => 1, "as" => 1,  "at" => 1, "back" => 1,
		"be" => 1,
		"but" => 1, "by" => 1, "call" => 1,
		"cannot" => 1, "cant" => 1, "co" => 1, "con" => 1, "couldnt" => 1, "cry" => 1, "de" => 1, "do" => 1, 
        "done" => 1, "down" => 1, "due" => 1, "during" => 1, "each" => 1, "eg" => 1, "else" => 1, "etc" => 1, "few" => 1,
        "for" => 1, "get" => 1, "go" => 1, "had" => 1, "has" => 1, "hasnt" => 1, "he" => 1, "her" => 1,
        "hers" => 1, "him" => 1, "his" => 1, "how" => 1, "ie" => 1, "if" => 1, "in" => 1, "inc" => 1, "is" => 1,
        "it" => 1, "its" => 1, "ltd" => 1, "me" => 1, "my" => 1, "no" => 1, "nor" => 1, "not" => 1, "of" => 1, "off" => 1,
        "on" => 1, "or" => 1, "our" => 1, "ours" => 1, "put" => 1, "re" => 1, "she" => 1, "so" => 1, "the" => 1,
        "this" => 1, "to" => 1, "un" => 1, "up" => 1, "us" => 1, "was" => 1, "we" => 1, "why" => 1, "you" => 1,
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
            if (count($keywords['all']) > 0) {
                $types = self::config()->search_types;
                $results = ArrayList::create();
                foreach ($types as $type) {
                    $typeResults = $type::get()->filterAny([
                        'Title:PartialMatch' => $keywords['all'],
                        'Content:PartialMatch' => $keywords['all']
                    ])->sort('LastEdited DESC')->limit(100);
                    $results->merge($typeResults);
                }
            }
        }

        $collection = ArrayList::create();
		foreach ($results as $item) {
			$item->URL = $item->hasMethod('Link') ? $item->Link() : $item->URLSegment;
            $item->SearchScore = $this->scoreResult($item, $keywords);
            $collection->push($item);
		}

        $collection = $collection->sort('SearchScore DESC')->limit(40);

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