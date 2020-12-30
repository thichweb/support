<?php

namespace Thichweb;

class Toc
{

	private $markup;
	private $tocgen;

	public function __construct()
	{
		$this->markup  = new \TOC\MarkupFixer();
        $this->tocgen = new \TOC\TocGenerator();
	}

	/**
	 * Tìm và thêm thẻ ID vào các thẻ H
	 *
	 * @param string $content Nội dung bài viết
	 * @return string
	 */
	public function ganNeoNoiDung($content)
	{
		if (blank($content)) return;
		return $this->markup->fix($content);
	}

	/**
	 * Tạo ra "Mục lục" để View ra Web
	 *
	 * @param string $stringFixer Nội dung sau khi đã được ->ganNeoNoiDung()
	 * @return string|null
	 */
	public function taoToc($stringFixer)
	{
		if (Str::of($stringFixer)->contains('<h')) {
			return $this->tocgen->getHtmlMenu($stringFixer);
		}

		return;
	}
}
