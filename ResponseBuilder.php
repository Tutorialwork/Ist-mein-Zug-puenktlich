<?php


class ResponseBuilder{

    private $response;

    public function speechText($text){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechTextWithCard($text){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "Simple",
                    "title" => "Ist mein Zug pünktlich?",
                    "content" => $text
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechTextWithDifferentCard($text, $cardTitle, $cardText){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "Simple",
                    "title" => $cardTitle,
                    "content" => $cardText
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechTextAndReprompt($text, $promptText, $data){
        $this->response = ["version" => "1.0",
            "sessionAttributes" => $data,
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "shouldEndSession" => false,
                "reprompt" => [
                    "outputSpeech" =>  [
                        "type" => "SSML",
                        "text" => "<speak>".$promptText."</speak>",
                        "ssml" => "<speak>".$promptText."</speak>"
                    ],
                ],
            ]
        ];
    }

    public function speechImageCard($text, $cardTitle, $cardText, $cardImage){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "Standard",
                    "title" => $cardTitle,
                    "text" => $cardText,
                    "image" => [
                        "smallImageUrl" => $cardImage,
                        "largeImageUrl" => $cardImage
                    ]
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechPermissionsCard($text, $permissions){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>".$text."</speak>",
                    "ssml" => "<speak>".$text."</speak>"
                ],
                "card" => [
                    "type" => "AskForPermissionsConsent",
                    "permissions" => $permissions
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    public function speechAPL($text, $data){
        $this->response = ["version" => "1.0",
            "response" => [
                "outputSpeech" =>  [
                    "type" => "SSML",
                    "text" => "<speak>" . $text . "</speak>",
                    "ssml" => "<speak>" . $text . "</speak>"
                ],
                "card" => [
                    "type" => "Simple",
                    "title" => "Ist mein Zug pünktlich?",
                    "content" => $text
                ],
                "directives" => [
                    [
                        "type" => "Alexa.Presentation.APL.RenderDocument",
                        "token" => "delayScreen",
                        "document" => [
                            "type" => "APL",
                            "version" => "1.4",
                            "theme" => "dark",
                            "import" => [
                                [
                                    "name" => "alexa-layouts",
                                    "version" => "1.2.0"
                                ]
                            ],
                            "mainTemplate" => [
                                "parameters" => [],
                                "items" => [
                                    [
                                        "type" => "AlexaTextList",
                                        "id" => "cheeseList",
                                        "headerTitle" => "Ist mein Zug pünktlich?",
                                        "headerBackButton" => false,
                                        "headerAttributionImage" => "https://images-na.ssl-images-amazon.com/images/I/41UP4f3sPGL.png",
                                        "backgroundImageSource" => "https://i.imgur.com/X6Rjzsn.jpeg",
                                        "backgroundBlur" => false,
                                        "backgroundColorOverlay" => true,
                                        "listItems" => $data,
                                    ]
                                ]
                            ],
                        ],
                    ]
                ],
                "shouldEndSession" => true
            ]
        ];
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }


}