all: example.pdf example.twig.pdf howto.pdf

example.pdf: example.tex
	lualatex --interaction=batchmode --halt-on-error example.tex
	
example.tex: example.twig.tex example.php
	./example.php > example.tex

example.twig.tex: example.twig.lyx
	lyx -batch -e luatex example.twig.lyx

example.twig.pdf: example.twig.lyx
	lyx -batch -e pdf example.twig.lyx

howto.pdf: howto.lyx example.twig.pdf example.pdf
	lyx -batch -e pdf5 howto.lyx


clean:
	rm example.tex example.pdf example.aux example.log example.twig.tex example.twig.pdf howto.pdf

.PHONY: all clean

