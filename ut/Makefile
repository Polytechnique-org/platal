files=$(addprefix test-,$(wildcard *.php))

test: $(files)
	echo "$(files)"

test-%:
	phpunit $*

.PHONY: test test-%
