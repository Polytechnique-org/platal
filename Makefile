
# $Id: Makefile,v 1.5 2004/11/25 20:18:39 x99laine Exp $
################################################################################
#Commit reference of https://github.com/googlemaps/js-marker-clusterer for script & image download
MAPS_MARKER_CLUSTERER_VERSION=dc326b9b9234ce964861eb35b416a6be1ca7d7cf

# definitions

VERSNUM := $(shell grep VERSION ChangeLog | head -1 | sed -e "s/VERSION //;s/ .*//")
VERSTAG := $(shell grep VERSION ChangeLog | head -1 | grep 'XX' > /dev/null 2> /dev/null && echo 'beta')

VERSION = $(VERSNUM)$(VERSTAG)

PKG_NAME = platal
PKG_DIST = $(PKG_NAME)-$(VERSION)
PKG_FILES = AUTHORS ChangeLog COPYING README Makefile
PKG_DIRS = configs htdocs include install.d plugins po scripts templates upgrade

VCS_FILTER = ! -name .arch-ids ! -name CVS

define download
@echo "Downloading $@ from $(DOWNLOAD_SRC)"
wget $(DOWNLOAD_SRC) -O $@ -q || ($(RM) $@; exit 1)
endef

################################################################################
# global targets

all: build

build: core conf static banana wiki openid medals jquery maps raven

check:
	@!(find . -name '*.php' -exec php -l {} ";" | grep -v 'No syntax errors detected')

test:
	make -C core test

q:
	@echo -e "Code statistics\n"
	@sloccount $(filter-out wiki/ spool/, $(wildcard */)) 2> /dev/null | egrep '^[a-z]*:'

%: %.in Makefile ChangeLog
	sed -e 's,@VERSION@,$(VERSION),g' $< > $@

up: update
update:
	@git fetch && git rebase `git symbolic-ref HEAD | sed -e 's~refs/heads/~origin/~'` && git submodule update

doc:
	@doxygen core/doc/doxygen.cfg

################################################################################
# targets

##
## core
##

core:
	[ -d core/.git ] || ( git submodule init && git submodule update )
	make -C core all

##
## conf
##

conf: spool/templates_c spool/mails_c classes/platalglobals.php configs/platal.cron htdocs/.htaccess spool/conf spool/tmp spool/banana

spool/templates_c spool/mails_c spool/uploads spool/conf spool/tmp spool/run spool/banana:
	mkdir -p $@
	chmod o+w $@

htdocs/.htaccess: htdocs/.htaccess.in Makefile
	@REWRITE_BASE="/~$$(id -un)"; \
	test "$$REWRITE_BASE" = "/~web" && REWRITE_BASE="/"; \
	sed -e "s,@REWRITE_BASE@,$$REWRITE_BASE,g" $< > $@

##
## static content
##
static: htdocs/javascript/core.js htdocs/javascript@VERSION htdocs/javascript/json2.js

htdocs/javascript/core.js:
	cd htdocs/javascript/ && ln -s ../../core/htdocs/javascript/core.js

%@VERSION: % Makefile ChangeLog
	cd $< && rm -f $(VERSION) && ln -sf . $(VERSION)

htdocs/javascript/json2.js: DOWNLOAD_SRC = https://github.com/douglascrockford/JSON-js/raw/master/json2.js --no-check-certificate
htdocs/javascript/json2.js:
	@$(download)

##
## wiki
##

WIKI_NEEDS = \
     wiki/local/farmconfig.php      \
     wiki/pub/skins/empty           \
     wiki/cookbook/e-protect.php    \
     spool/wiki.d                   \
     htdocs/uploads                 \
     htdocs/wiki                    \

wiki: get-wiki build-wiki

build-wiki: $(WIKI_NEEDS) | get-wiki

htdocs/uploads:
	cd htdocs && ln -sf ../spool/uploads

htdocs/wiki:
	cd htdocs && ln -sf ../wiki/pub wiki


spool/wiki.d:
	mkdir -p $@
	chmod o+w $@
	cd $@ && ln -sf ../../include/wiki/wiki.d/* .


wiki/cookbook/e-protect.php:
	cd wiki/cookbook && ln -sf ../../include/wiki/e-protect.php

wiki/local/farmconfig.php:
	cd wiki/local/ && ln -sf ../../include/wiki/farmconfig.php

wiki/pub/skins/empty:
	cd wiki/pub/skins/ && ln -sf ../../../include/wiki/empty


get-wiki:
	@if ! test -d wiki; then                                          \
		wget http://www.pmwiki.org/pub/pmwiki/pmwiki-latest.tgz;      \
		tar -xzvf pmwiki-latest.tgz;                                  \
		rm pmwiki-latest.tgz;                                         \
		mv pmwiki-* wiki;                                             \
	fi

##
## openid
##

openid:
	-rm -rf include/Auth

##
## banana
##
banana: htdocs/images/banana htdocs/css/banana.css
htdocs/images/banana: banana-sub
	cd $(@D) && ln -snf ../../banana/img $(@F)

htdocs/css/banana.css: banana-sub
	cd $(@D) && ln -snf ../../banana/css/style.css $(@F)

banana-sub:
	make -C banana

##
## Medal thumbnails
##
MEDAL_PICTURES=$(wildcard htdocs/images/medals/*.jpg)
MEDAL_THUMBNAILS=$(subst /medals/,/medals/thumb/,$(MEDAL_PICTURES))

medals: $(MEDAL_THUMBNAILS)

$(MEDAL_THUMBNAILS): $(subst /medals/thumb/,/medals/,$(@F))
	convert -resize x50 $(subst /medals/thumb/,/medals/,$@) $@

##
## jquery
##
JQUERY_VERSION=1.5.1
JQUERY_COLOR_VERSION=2.1.2
JQUERY_PLUGINS=color form
JQUERY_PLUGINS_PATHES=$(addprefix htdocs/javascript/jquery.,$(addsuffix .js,$(JQUERY_PLUGINS)))

JQUERY_UI_VERSION=1.8.10
JQUERY_UI=core widget tabs datepicker autocomplete position
JQUERY_UI_PATHES=$(addprefix htdocs/javascript/jquery.ui.,$(addsuffix .js,$(JQUERY_UI)))

JQUERY_TMPL_VERSION=vBeta1.0.0
JQUERY_TMPL_PATH=htdocs/javascript/jquery.tmpl.js

JSTREE_VERSION=1.0rc2
JSTREE_PATH=htdocs/javascript/jquery.jstree.js

jquery: htdocs/javascript/jquery.xorg.js htdocs/javascript/jquery.ui.xorg.js $(JSTREE_PATH)

htdocs/javascript/jquery.xorg.js: htdocs/javascript/jquery.js $(JQUERY_PLUGINS_PATHES) $(JQUERY_TMPL_PATH)
	cat $^ > $@

htdocs/javascript/jquery.ui.xorg.js: $(JQUERY_UI_PATHES) htdocs/javascript/jquery.ui.datepicker-fr.js
	cat $^ > $@

htdocs/javascript/jquery-$(JQUERY_VERSION).min.js: DOWNLOAD_SRC = http://code.jquery.com/$(@F)
htdocs/javascript/jquery-$(JQUERY_VERSION).min.js:
	@-rm htdocs/javascript/jquery-*.min.js
	@$(download)

htdocs/javascript/jquery.js: htdocs/javascript/jquery-$(JQUERY_VERSION).min.js
	ln -snf $(<F) $@

htdocs/javascript/jquery.color-$(JQUERY_COLOR_VERSION).min.js: DOWNLOAD_SRC = http://code.jquery.com/color/$(@F)
htdocs/javascript/jquery.color-$(JQUERY_COLOR_VERSION).min.js:
	@-rm htdocs/javascript/jquery.color-*.min.js
	@$(download)

htdocs/javascript/jquery.color.js: htdocs/javascript/jquery.color-$(JQUERY_COLOR_VERSION).min.js
	ln -snf $(<F) $@

htdocs/javascript/jquery.form.js: DOWNLOAD_SRC = http://malsup.github.com/min/jquery.form.min.js
htdocs/javascript/jquery.form.js:
	@-rm htdocs/javascript/jquery.form*.js
	@$(download)

htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js: DOWNLOAD_SRC = http://jquery-ui.googlecode.com/svn/tags/$(JQUERY_UI_VERSION)/ui/minified/jquery.ui.$*.min.js
htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js:
	@$(download)

htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).datepicker-fr.js: DOWNLOAD_SRC = http://jquery-ui.googlecode.com/svn/tags/$(JQUERY_UI_VERSION)/ui/minified/i18n/jquery.ui.datepicker-fr.min.js
htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).datepicker-fr.js:
	@$(download)

$(JQUERY_UI_PATHES) htdocs/javascript/jquery.ui.datepicker-fr.js: htdocs/javascript/jquery.ui.%.js: htdocs/javascript/jquery.ui-$(JQUERY_UI_VERSION).%.js
	ln -snf $(<F) $@

htdocs/javascript/jquery.tmpl-$(JQUERY_TMPL_VERSION).js: DOWNLOAD_SRC = https://github.com/jquery/jquery-tmpl/raw/$(JQUERY_TMPL_VERSION)/jquery.tmpl.js --no-check-certificate
htdocs/javascript/jquery.tmpl-$(JQUERY_TMPL_VERSION).js:
	@-rm htdocs/javascript/jquery.tmpl*.js
	@$(download)

$(JQUERY_TMPL_PATH): htdocs/javascript/jquery.tmpl-$(JQUERY_TMPL_VERSION).js
	ln -snf $(<F) $@

$(JSTREE_PATH):
	rm -f htdocs/javascript/jquery.jstree-*.js
	mkdir spool/tmp/jstree
	wget http://jstree.googlecode.com/files/jsTree.v.$(JSTREE_VERSION).zip -O spool/tmp/jstree/jquery.jstree-$(JSTREE_VERSION).zip
	unzip spool/tmp/jstree/jquery.jstree-$(JSTREE_VERSION).zip -d spool/tmp/jstree/
	mv -f spool/tmp/jstree/themes/default/style.css htdocs/css/jstree.css
	mv -f spool/tmp/jstree/themes/default/d.png htdocs/images/jstree.png
	mv -f spool/tmp/jstree/jquery.jstree.js htdocs/javascript/jquery.jstree-$(JSTREE_VERSION).js
	sed -i -e 's/"d\.png"/"..\/images\/jstree.png"/' htdocs/css/jstree.css
	sed -i -e 's/"throbber\.gif"/"..\/images\/wait.gif"/' htdocs/css/jstree.css
	sed -i -e 's/#ffffee/inherit/' htdocs/css/jstree.css
	ln -snf jquery.jstree-$(JSTREE_VERSION).js htdocs/javascript/jquery.jstree.js
	rm -Rf spool/tmp/jstree

##
## Maps auxiliary scripts
##
maps: htdocs/javascript/markerclusterer.js htdocs/images/m1.png htdocs/images/m2.png htdocs/images/m3.png htdocs/images/m4.png htdocs/images/m5.png

## Download markerclusterer source and images (from https://github.com/googlemaps/js-marker-clusterer)
htdocs/javascript/markerclusterer.js:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/src/markerclusterer.js' -O $@
htdocs/images/m1.png:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/images/m1.png' -O $@
htdocs/images/m2.png:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/images/m2.png' -O $@
htdocs/images/m3.png:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/images/m3.png' -O $@
htdocs/images/m4.png:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/images/m4.png' -O $@
htdocs/images/m5.png:
	wget 'https://raw.githubusercontent.com/googlemaps/js-marker-clusterer/$(MAPS_MARKER_CLUSTERER_VERSION)/images/m5.png' -O $@


##
## Raven-js
##
RAVEN_VERSION=1.1.2
raven: $(addprefix htdocs/javascript/raven.,min.js min.map js)

# Documentation: http://raven-js.readthedocs.org/en/latest/install/index.html
htdocs/javascript/raven.%: DOWNLOAD_SRC = http://cdn.ravenjs.com/$(RAVEN_VERSION)/$(@F)
htdocs/javascript/raven.%:
	@-rm $@
	$(download)


##
## lists rpc
##
start-listrpc: spool/run
	sudo -u list /sbin/start-stop-daemon --pidfile spool/run/listrpc.pid -m -b -x $$PWD/bin/lists.rpc.py --start
	@sleep 2
	@sudo -u list kill -0 $$(cat spool/run/listrpc.pid)

start-listrpc-fg: spool/run
	sudo -u list ./bin/lists.rpc.py

stop-listrpc:
	-sudo -u list /sbin/start-stop-daemon --pidfile spool/run/listrpc.pid --stop
	@-rm -f spool/run/listrpc.pid

restart-listrpc: stop-listrpc start-listrpc

################################################################################

.PHONY: all build dist clean core http* q check test
.PHONY: wiki build-wiki get-wiki
.PHONY: openid
.PHONY: banana banana-sub htdocs/images/banana htdocs/css/banana.css
.PHONY: medals jquery maps raven
.PHONY: start-listrpc start-listrpc-fg stop-listrpc restart-listrpc
.PHONY: up update doc
