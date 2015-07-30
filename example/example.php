#!/usr/bin/env php
<?php
/*
 * Copyright (c) 2013, Josef Kufner  <jk@frozen-doe.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the author nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */

/* Use exceptions instead of errors */
set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
	if (error_reporting()) {
		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
});


require dirname(dirname(__FILE__)).'/vendor/autoload.php';

/*
 * Twig initialization
 */
$lyxtwig = new LyxOnTwig\LyxOnTwig('./');

/*
 * Render template
 */
echo $lyxtwig->renderPdf('example.twig.tex', 'example.pdf',
	array('items' => array_map(function($x) { $x['y'] = ($x['a'] * $x['a'] + $x['b'] * $x['b']) / $x['c']; return $x; }, array(
		'first' => array(
			'label' => 'First label',
			'description' => 'First description.',
			'a' => 3,
			'b' => 4,
			'c' => 5,
		),
		'second' => array(
			'label' => 'Second label',
			'description' => "Second description with empty line and some ugly characters:\n\n"
					."With spaces: { \\ & % $ # _ ~ ^ } and without spaces {\\&%$#_~^}.",
			'a' => 5,
			'b' => 12,
			'c' => 13,
		),
		'third' => array(
			'label' => 'Third label',
			'description' => 'Third description: Příliš žluťoučký kůň úpěl ďábelské ódy.',
			'a' => 7,
			'b' => 24,
			'c' => 25,
		),
	))));

