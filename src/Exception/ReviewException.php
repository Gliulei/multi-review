<?php
/**
 * Created by PhpStorm.
 * User: liulei
 * Date: 2019/10/3
 * Time: 8:59 PM
 */
namespace Review\Exception;

class ReviewException extends \Exception
{
    public function __construct($details)
    {
        if (is_array($details)) {
            $message = json_encode($details);
        } else {
            $message = $details;
        }

        parent::__construct($message);

    }
}