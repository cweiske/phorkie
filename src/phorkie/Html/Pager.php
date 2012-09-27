<?php
namespace phorkie;

class Html_Pager
{
    protected $pager;

    /**
     * Create a new pager
     *
     * @param integer $itemCount   Number of items in total
     * @param integer $perPage     Number of items on one page
     * @param integer $currentPage Current page, beginning with 1
     * @param string  $filename    URL the page number shall be appended
     */
    public function __construct($itemCount, $perPage, $currentPage, $filename)
    {
        $append = true;
        if (strpos($filename, '%d') !== false) {
            $append = false;
        }
        //fix non-static factory method error
        error_reporting(error_reporting() & ~E_STRICT);
        $this->pager = \Pager::factory(
            array(
                'mode'        => 'Sliding',
                'perPage'     => $perPage,
                'delta'       => 2,
                'totalItems'  => $itemCount,
                'currentPage' => $currentPage,
                'urlVar'      => 'page',
                'append'      => $append,
                'path'        => '/',
                'fileName'    => $filename,
                'separator'   => '###',
                'spacesBeforeSeparator' => 0,
                'spacesAfterSeparator' => 0,
                'curPageSpanPre' => '',
                'curPageSpanPost' => '',
                'firstPagePre' => '',
                'firstPageText' => 'first',
                'firstPagePost' => '',
                'lastPagePre' => '',
                'lastPageText' => 'last',
                'lastPagePost' => '',
                'prevImg' => '« prev',
                'nextImg' => 'next »',
            )
        );
    }


    public function getLinks()
    {
        $arLinks = $this->pager->getLinks();
        $arLinks['pages'] = explode('###', $arLinks['pages']);
        return $arLinks;
    }

    public function numPages()
    {
        return $this->pager->numPages();
    }
}

?>
