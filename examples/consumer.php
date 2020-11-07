<?php

// example webhook consumer

// raw email read from STDIN
$email = file_get_contents("php://input");

// using PhpMimeMailParser to parse
// https://github.com/php-mime-mail-parser/php-mime-mail-parser

$Parser = new PhpMimeMailParser\Parser();
$Parser->setText($email);

// now process

$rawHeaderTo = $parser->getHeader('to');
// return "test" <test@example.com>, "test2" <test2@example.com>

$rawHeaderFrom = $parser->getHeader('from');
// return "test" <test@example.com>

$text = $parser->getMessageBody('text');
// return the text version

$html = $parser->getMessageBody('html');
// return the html version

$attachments = $parser->getAttachments();
// return an array of all attachments (include inline attachments)

// etc ...

