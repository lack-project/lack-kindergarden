<?php

namespace Lack\Kindergarden\Chat;

/**
 * Predication Reponse Format to edit existing content (gpt-4o and above)
 *
 * Enables Predicted Outputs
 *
 */
class PredictionChatResponseFormat implements ChatResponseFormat
{


    public function __construct(
        /**
         * The content that should be edited
         */
        public readonly string $content)
    {

    }

}
