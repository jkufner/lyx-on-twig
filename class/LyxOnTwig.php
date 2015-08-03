<?php
/*
 * Copyright (c) 2013-2015 Josef Kufner  <josef@kufner.cz>
 * All rights reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace LyxOnTwig;

class LyxOnTwig {

	protected $loader;
	protected $twig;
	protected $lexer;

	public $luatex_command = 'lualatex --interaction=batchmode --halt-on-error --output-format=pdf --output-directory=%s %s';

	/**
	 * Initialize Twig
	 *
	 * @param $template_path Path to directory where LaTeX templates are
	 * 	located.
	 * @param $config Additional config options passed to Twig_Environment.
	 * 	The `autoescape` is forced to `"tex"`.
	 */
	public function __construct($template_path = './', $config = array())
	{
		$config['autoescape'] = 'tex';

		$this->loader = new \Twig_Loader_Filesystem($template_path);
		$this->twig = new \Twig_Environment($this->loader, $config);

		$this->lexer = new \Twig_Lexer($this->twig, array(
			'tag_comment'   => array('%%{', '%%}'),
			'tag_block'     => array('\protect\TwigBlock{', '}'),
			'tag_variable'  => array('\Twig{', '}'),
			'interpolation' => array('#[', ']'),	// cannot use {} inside of Twig block and variable
		));
		$this->twig->setLexer($this->lexer);
		$this->twig->getExtension('core')->setEscaper('tex', function(\Twig_Environment $env, $string, $strategy = 'tex', $charset = null, $autoescape = false) {
			return LyxOnTwig::latexspecialchars($string);
		});
	}


	/**
	 * Render template to TeX file
	 */
	public function renderTex($template_filename, $target_tex_filename, $data)
	{
		return file_put_contents($target_tex_filename, $this->twig->render($template_filename, $data));
	}


	/**
	 * Render template to PDF file (via invoking lualatex, intermediate tex code is stored in temporary file)
	 */
	public function renderPdf($template_filename, $target_pdf_filename, $data)
	{
		$temp_dir = tempnam(sys_get_temp_dir(), 'LyxOnTwig.');
		unlink($temp_dir);
		mkdir($temp_dir);	// FIXME

		$base_filename = $temp_dir.'/'.basename($template_filename);
		$tex_file = $base_filename.'.tex';
		$pdf_file = $base_filename.'.pdf';

		// Render TeX to string
		$this->renderTex($template_filename, $tex_file, $data);

		// Run luatex
		$cmd = sprintf($this->luatex_command, escapeshellarg($temp_dir), escapeshellarg($tex_file));
		echo "# $cmd\n";
		exec($cmd, $out, $ret);

		echo "\t", join("\n\t", $out), "\n";
		if ($ret == 0) {
			rename($pdf_file, $target_pdf_filename);
		} else {
			@unlink($pdf_file);
		}

		// Clean up
		foreach (scandir($temp_dir) as $f) {
			$fn = $temp_dir.'/'.$f;
			if ($f != '.' && $f != '..' && is_file($fn)) {
				unlink($fn);
			}
		}
		rmdir($temp_dir);

		return ($ret == 0);
	}



	/**
	 * Get twig.
	 */
	public function getTwig()
	{
		return $this->twig;
	}


	/**
	 * Escape special chars for LaTeX code (just like htmlspecialchars).
	 */
	final public static function latexspecialchars($string)
	{
		return '{'.preg_replace_callback("/[\^\%~\\\\#\$%&_\{\}]/", function($token) {
				switch($token[0]) {
					case  '{': return '\{';
					case  '}': return '\}';
					case  '&': return '\&';
					case  '%': return '\%';
					case  '$': return '\$';
					case  '_': return '\_';
					case  '#': return '\#';
					case  '^': return '\textasciicircum{}';
					case  '~': return '\textasciitilde{}';
					case '\\': return '\textbackslash{}';
					default: throw new \InvalidArgumentException('This should never happen. $token = '.var_export($token, true));
				}
			}, $string).'}';
	}

};

