################################################################################
# definitions

VERSNUM := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/ .*//")
VERSTAG := $(shell grep VERSION ChangeLog | head -1 | grep 'XX' > /dev/null 2> /dev/null && echo 'beta')
VERSION = $(VERSNUM)$(VERSTAG)

################################################################################
# global targets

all: build

build: include/version.inc.php

clean:
	-rm include/version.inc.php

q:
	@echo -e "Code statistics\n"
	@sloccount $(wildcard */) 2> /dev/null | egrep '^[a-z]*:'

################################################################################
# targets

%: %.in Makefile ChangeLog
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

include/version.inc.php: Makefile ChangeLog
	echo '<?php define("PLATAL_CORE_VERSION", "${VERSION}"); ?>' > $@

.PHONY: build dist clean q
