<?php
/*
  Plugin Name: Sentence To SEO (keywords, description and tags)
  Version: 1.0
  Plugin URI:
  Description: Convert any sentence into SEO components such as Keywords meta description and tags
  Author: Yarob Al-Taay
  Author URI: https://eazyserver.net
 */

class SentenceToSEO
{

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    public static $STOP_WORDS = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "and", "another", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "are", "around", "as", "at", "back", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom", "but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    private $permanentKeywords = [];
    private $stopwords = [];

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));

        $this->permanentKeywords = SentenceToSEO::getPermanentKeywords();
        $this->stopwords = SentenceToSEO::getStopWords();
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
                'SentenceToSEO', 'SentenceToSEO', 'manage_options', 'SentenceToSEO', array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        $filteredInput = filter_input_array(INPUT_POST);
        ?>
        <style>
            table {width:60%;}
            @media screen and (max-width: 600px) {
                table {width:100%;}
                thead {display: none;}
                tr:nth-of-type(2n) {background-color: inherit;}
                tr td:first-child {background: #f0f0f0; font-weight:bold;font-size:1.3em;}
                tbody td {display: block;  text-align:center;}
                tbody td:before { 
                    content: attr(data-th); 
                    display: block;
                    text-align:center;  
                }
            }</style>
        <div class="wrap">
            <h2><?php '<h2>' . _e('Sentence To SEO') . '</h2>' ?></h2>           
            <form method="post" action="">

                <table>
                    <tbody>
                        <tr>
                            <td><?php _e("Sentence: "); ?></td>
                            <td>
                                <textarea name="sentence" placeholder="Sentence" cols="80" rows="10" required=""><?= @$filteredInput['sentence'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Permanent keywords: "); ?></td>
                            <td>
                                <textarea name="permanent_keywords" placeholder="Permanent keywords comma ',' separated..." cols="80" rows="4"><?= @$filteredInput['permanent_keywords'] ? $filteredInput['permanent_keywords'] : implode(', ', $this->permanentKeywords) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Stopwords (ignore list): "); ?></td>
                            <td>
                                <textarea name="stopwords" cols="80" rows="4"><?= @$filteredInput['stopwords'] ? $filteredInput['stopwords'] : implode(', ', $this->stopwords) ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button('Convert to SEO'); ?>
            </form>


            <?php
            if (isset($filteredInput['submit']) and $filteredInput['submit'] == 'Convert to SEO')
            {
                $this->stopwords = explode(',', $filteredInput['stopwords']);

                SentenceToSEO::updateStopWords($this->stopwords);

                $this->permanentKeywords = explode(',', $filteredInput['permanent_keywords']);
                SentenceToSEO::updatePermanentKeywords($this->permanentKeywords);

                $seo = SentenceToSEO::stringToSEO($filteredInput['sentence'] . ', ' . $filteredInput['permanent_keywords'], $this->stopwords);
                ?><table>
                    <tbody>
                        <tr>
                            <td><?php _e("SEO Description: "); ?></td>
                            <td>
                                <textarea cols="50" rows="10"><?= $seo['description'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Keywords: "); ?></td>
                            <td>
                                <textarea cols="50" rows="10"><?= $seo['keywords'] ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Tags: "); ?></td>
                            <td>
                                <textarea cols="50" rows="2"><?= $seo['tags'] ?></textarea>
                            </td>
                        </tr>

                    </tbody>
                </table><?php
        }
        ?>


        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
                'my_option_group', // Option group
                'my_option_name', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'setting_section_id', // ID
                'SentenceToSEO', // Title
                array($this, 'print_section_info'), // Callback
                'SentenceToKeywords' // Page
        );

        add_settings_field(
                'id_number', // ID
                'ID Number', // Title 
                array($this, 'id_number_callback'), // Callback
                'SentenceToSEO', // Page
                'setting_section_id' // Section           
        );

        add_settings_field(
                'title', 'Title', array($this, 'title_callback'), 'SentenceToKeywords', 'setting_section_id'
        );
    }

    public static function stringToSEO($text = NULL, $stopwords = [])
    {
        $keywords = preg_replace('/[^A-Za-z0-9\s]/', '', $text);
        $keywords = explode(' ', $keywords);

        $keywordsCounter = [];

        function inArray($needle, $haystack)
        {
            foreach ($haystack as $v)
            {
                if (trim(strtolower($needle)) == trim(strtolower($v)))
                {
                    return true;
                }
            }
            return FALSE;
        }

        function tokenTruncate($string, $your_desired_width)
        {
            $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
            $parts_count = count($parts);

            $length = 0;
            $last_part = 0;
            for (; $last_part < $parts_count; ++$last_part)
            {
                $length += strlen($parts[$last_part]);
                if ($length > $your_desired_width)
                {
                    break;
                }
            }

            return implode(array_slice($parts, 0, $last_part));
        }

        foreach ($keywords as $keyword)
        {
            if (strlen($keyword) > 2 and ! inArray(($keyword), $stopwords))
            {
                $keyword = strtolower($keyword);

                $keywordsCounter[$keyword] = isset($keywordsCounter[$keyword]) ? $keywordsCounter[$keyword] + 1 : 1;
            }
        }

        //arsort($keywordsCounter);

        $tags = [];
        foreach ($keywordsCounter as $k => $v)
        {
            if ($v > 1)
            {
                $tags[] = $k;
            }
        }

        return array(
            'keywords' => implode(', ', array_keys($keywordsCounter)),
            'tags' => implode(', ', $tags),
            'description' => tokenTruncate($text, 160)
        );
    }

    public static function getStopWords()
    {
        return get_option('sentenceToKeywords_stopwords', SentenceToSEO::$STOP_WORDS);
    }

    public static function updateStopWords($stopWords = [])
    {
        if (is_array($stopWords))
        {
            update_option('sentenceToKeywords_stopwords', $stopWords, FALSE);
        }
    }

    public static function getPermanentKeywords()
    {
        return get_option('sentenceToSEO_permanentKeywords', []);
    }

    public static function updatePermanentKeywords($stopWords = [])
    {
        if (is_array($stopWords))
        {
            foreach ($stopWords as $k => $v)
            {
                $stopWords[$k] = trim($v);
            }
            update_option('sentenceToSEO_permanentKeywords', $stopWords, FALSE);
        }
    }

    function getAminNotice($msg = '', $type = 'updated')
    {
        ?><div class="<?= $type ?>">
            <p><?php _e($msg); ?></p>
        </div><?php
    }

}

if (is_admin())
{
    $my_settings_page = new SentenceToSEO();
}