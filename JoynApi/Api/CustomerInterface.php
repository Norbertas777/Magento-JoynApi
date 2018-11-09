<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.6
 * Time: 10.15
 */
namespace Trollweb\JoynApi\Api;

interface CustomerInterface
{
    /**
     * POST for attribute api
     * @param mixed $param
     * @return array
     */

    public function create();

    /**
     * PUT for attribute api
     * @param mixed $param
     * @return array
     */

    public function update();

    /**
     * DELETE for attribute api
     * @param mixed $param
     * @return array
     */

    public function delete();

}