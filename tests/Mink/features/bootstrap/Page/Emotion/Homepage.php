<?php

namespace  Shopware\Tests\Mink\Page\Emotion;

use Behat\Mink\WebAssert;
use Shopware\Tests\Mink\Element\Emotion\Article;

use Shopware\Tests\Mink\Element\Emotion\Banner;

use Shopware\Tests\Mink\Element\Emotion\BlogArticle;
use Shopware\Tests\Mink\Element\Emotion\CategoryTeaser;
use Shopware\Tests\Mink\Element\Emotion\CompareColumn;

use Shopware\Tests\Mink\Element\Emotion\YouTube;

use Shopware\Tests\Mink\Element\SliderElement;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Homepage extends Page implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'newsletterForm' => 'div.footer_column.col4 > form',
            'newsletterFormSubmit' => 'div.footer_column.col4 > form input[type="submit"]'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array();
    }

    /**
     * Searches the given term in the shop
     * @param string $searchTerm
     */
    public function searchFor($searchTerm)
    {
        $data = array(
            array(
                'field' => 'sSearch',
                'value' => $searchTerm
            )
        );

        $searchForm = $this->getElement('SearchForm');
        $language = Helper::getCurrentLanguage($this);
        Helper::fillForm($searchForm, 'searchForm', $data);
        Helper::pressNamedButton($searchForm, 'searchButton', $language);
        $this->verifyResponse();
    }

    /**
     * Search the given term using live search
     * @param $searchTerm
     */
    public function receiveSearchResultsFor($searchTerm)
    {
        $data = array(
            array(
                'field' => 'sSearch',
                'value' => $searchTerm
            )
        );

        $searchForm = $this->getElement('SearchForm');
        Helper::fillForm($searchForm, 'searchForm', $data);
        $this->getSession()->wait(5000, "$('ul.searchresult').children().length > 0");
    }

    /**
     * @param string $keyword
     */
    public function receiveNoResultsMessageForKeyword($keyword)
    {
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains(sprintf(
            'Leider wurden zu "%s" keine Artikel gefunden',
            $keyword
        ));
    }

    /**
     * Changes the currency
     * @param string $currency
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function changeCurrency($currency)
    {
        $this->pressButton($currency);
    }

    /**
     * @param array $data
     */
    public function subscribeNewsletter(array $data)
    {
        Helper::fillForm($this, 'newsletterForm', $data);

        $locators = array('newsletterFormSubmit');
        $elements = Helper::findElements($this, $locators);
        $elements['newsletterFormSubmit']->press();
    }

    /**
     * Checks the product comparison
     * Available properties are: image, name, ranking, description, price, link
     *
     * @param CompareColumn $compareColumns
     * @param array $items
     */
    public function checkComparisonProducts(CompareColumn $compareColumns, array $items)
    {
        if (count($compareColumns) !== count($items)) {
            $message = sprintf(
                'There are %d products in the comparison! (should be %d)',
                count($compareColumns),
                count($items)
            );
            Helper::throwException($message);
        }

        $result = Helper::searchElements($items, $compareColumns);

        if ($result !== true) {
            $messages = array('The following articles were not found:');
            foreach ($result as $product) {
                $messages[] = $product['name'];
            }
            Helper::throwException($messages);
        }
    }

    /**
     * Checks an emotion banner with or without link
     * @param Banner $banner
     * @param string $image
     * @param string|null $link
     */
    public function checkLinkedBanner(Banner $banner, $image, $link = null)
    {
        $properties = [
            'image' => $image
        ];

        if (!is_null($link)) {
            $properties['link'] = $link;
        }

        $result = Helper::assertElementProperties($banner, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The banner %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Checks an emotion banner with mapping
     * @param Banner $banner
     * @param string $image
     * @param string[] $mapping
     */
    public function checkMappedBanner(Banner $banner, $image, array $mapping)
    {
        $this->checkLinkedBanner($banner, $image);

        $bannerMapping = $banner->getMapping();
        $result = Helper::compareArrays($bannerMapping, $mapping);

        if ($result === true) {
            return;
        }

        $message = [
            'The banner mappings are different!',
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2']
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion blog element
     * @param BlogArticle $blogArticle
     * @param array $articles
     * @throws \Behat\Behat\Exception\PendingException
     * @throws \Exception
     */
    public function checkBlogArticles(BlogArticle $blogArticle, $articles)
    {
        $properties = array_keys(current($articles));

        $blogArticles = $blogArticle->getArticles($properties);

        $result = Helper::compareArrays($blogArticles, $articles);

        if ($result === true) {
            return;
        }

        $message = [
            sprintf('The slides have a different %s!', $result['key']),
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2']
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion Youtube element
     * @param YouTube $youtube
     * @param string $code
     * @throws \Exception
     */
    public function checkYoutubeVideo(YouTube $youtube, $code)
    {
        $result = Helper::assertElementProperties($youtube, ['code' => $code]);

        if ($result === true) {
            return;
        }

        $message = [
            'The YouTube video has a different code!',
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2']
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion slider element
     * @param SliderElement $slider
     * @param array $slides
     */
    public function checkSlider(SliderElement $slider, array $slides)
    {
        $properties = array_keys(current($slides));

        $sliderSlides = $slider->getSlides($properties);

        $result = Helper::compareArrays($sliderSlides, $slides);

        if ($result === true) {
            return;
        }

        $message = [
            sprintf('The slides have a different %s!', $result['key']),
            'Given: ' . $result['value'],
            'Expected: ' . $result['value2']
        ];

        Helper::throwException($message);
    }

    /**
     * Checks an emotion category teaser element
     * @param CategoryTeaser $teaser
     * @param string $name
     * @param string $image
     * @param string $link
     */
    public function checkCategoryTeaser(CategoryTeaser $teaser, $name, $image, $link)
    {
        $properties = [
            'name' => $name,
            'image' => $image,
            'link'  => $link
        ];

        $result = Helper::assertElementProperties($teaser, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The category teaser %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Checks an emotion article element
     * @param Article $article
     * @param array $data
     */
    public function checkArticle(Article $article, array $data)
    {
        $properties = Helper::convertTableHashToArray($data);
        $properties = Helper::floatArray($properties, ['price']);

        $result = Helper::assertElementProperties($article, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The article %s is "%s" (should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }
}