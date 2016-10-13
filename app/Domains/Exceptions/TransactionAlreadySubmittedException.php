<?php

namespace App\Domains\Exceptions;


/**
 * Class TransactionAlreadySubmittedException
 * @package App\Domains\Exceptions
 * Exception that happens when a forbidden operation is commenced when the transaction had been submitted,
 * example: add product after already give the payment proof.
 */
class TransactionAlreadySubmittedException extends \Exception
{

}