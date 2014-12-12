<?php namespace Esensi\Core\Contracts;

/**
 * Comfirmable controller interface
 *
 * @author daniel <daniel@bexarcreative.com>
 */
interface ConfirmableControllerInterface{

    /**
     * Display a confirmation modal for the specified resource action.
     *
     * @param string $action
     * @param integer $id (optional)
     * @return void
     */
    public function confirm($action, $id = null);

    /**
     * Display a confirmation modal for the specified resource bulk action.
     *
     * @param string $action
     * @param string|array $ids (optional)
     * @return void
     */
    public function confirmBulk($action, $ids = null);

}