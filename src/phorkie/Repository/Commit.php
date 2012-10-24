<?php
namespace phorkie;


class Repository_Commit
{
    public $hash;
    public $committerName;
    public $committerEmail;
    public $committerTime;

    public $linesAdded;
    public $linesDeleted;
    public $filesChanged;


    public function getIconUrl()
    {
        $hh = new HtmlHelper();
        return $hh->getIconUrl($this->committerEmail);
    }

    /**
     * @return array Array with 7 fields, each has either "r", "g" or "n"
     *               ("red", "green" or "none")
     */
    public function getDots()
    {
        $r = $this->getDotNum($this->linesDeleted);
        $g = $this->getDotNum($this->linesAdded);
        $sum = $r + $g;
        if ($sum > 7) {
            $quot = ceil($sum / 7);
            $r = intval($r / $quot);
            $g = intval($g / $quot);
        }
        $string = str_repeat('g', $g)
            . str_repeat('r', $r)
            . str_repeat('n', 7 - $g - $r);

        return str_split($string);
    }

    public function getDotNum($lines)
    {
        if ($lines == 0) {
            return 0;
        } else if ($lines == 1) {
            return 1;
        } else if ($lines == 2) {
            return 2;
        } else if ($lines == 3) {
            return 3;
        } else if ($lines == 4) {
            return 4;
        } else if ($lines < 10) {
            return 5;
        } else if ($lines < 50) {
            return 6;
        }
        return 7;
    }
}

?>
