#!/usr/bin/env python
#***************************************************************************
#*  Copyright (C) 2003-2018 Polytechnique.org                              *
#*  http://opensource.polytechnique.org/                                   *
#*                                                                         *
#*  This program is free software; you can redistribute it and/or modify   *
#*  it under the terms of the GNU General Public License as published by   *
#*  the Free Software Foundation; either version 2 of the License, or      *
#*  (at your option) any later version.                                    *
#*                                                                         *
#*  This program is distributed in the hope that it will be useful,        *
#*  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
#*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
#*  GNU General Public License for more details.                           *
#*                                                                         *
#*  You should have received a copy of the GNU General Public License      *
#*  along with this program; if not, write to the Free Software            *
#*  Foundation, Inc.,                                                      *
#*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
#***************************************************************************

import sys, random, re

########################################################################

# A random word generator using Markov chains

class WordGenerator:

    def __init__(self, order=3, special=u'\n'):
        self.order = order
        self.special = special
        self.markov = {}

    def load(self, corpus):
        for word in corpus:
            word = self.special * self.order + word.strip() + self.special
            for pos in range(len(word) - self.order):
                prefix = word[pos:pos + self.order]
                suffix = word[pos + self.order]
                if not self.markov.has_key(prefix):
                    self.markov[prefix] = []
                self.markov[prefix].append(suffix)

    def generate(self):
        word = self.special * self.order
        while True:
            c = random.choice(self.markov[word[-self.order:]])
            if c == self.special:
                return word[self.order:]
            else:
                word += c

########################################################################

def parse_aliases(file):
    firstnames = []
    lastnames = []
    promos = []
    handle = open(file, 'r') # aliases are ASCII only
    aliases = handle.readlines()
    handle.close()
    aliases.sort()
    alias_re = re.compile(r'([a-z\-]+).([a-z\-]+).([0-9]{4})')
    for alias in aliases:
        alias = alias.rstrip()
        match = alias_re.match(alias)
        if match is None:
            print "Warning: could not parse alias '%s'" % alias
        else:
            firstnames.append(match.group(1))
            lastnames.append(match.group(2))
            promos.append(match.group(3))
    handle.close()
    return firstnames, lastnames, promos

# Returns the index of the first value of `array' strictly greater than `value'
def find_next(value, array, pmin=0, pmax=-1):
    if pmax == -1: pmax = len(array)
    if pmax == pmin + 1: return pmax
    # At every step, array[pmin] < value < array[pmax]
    pint = (pmin + pmax) / 2
    if array[pint] < value:
        return find_next(value, array, pint, pmax)
    else:
        return find_next(value, array, pmin, pint)

def create_alias(firstname, pred_lastname, succ_lastname, rand_lastnames):
    i_pred = find_next(pred_lastname, rand_lastnames)
    i_succ = find_next(succ_lastname, rand_lastnames)
    # We don't know the order of the names
    if i_pred > i_succ: i_pred, i_succ = i_succ, i_pred
    # Hack in edge case
    if i_pred == i_succ:
        lastname = "%s-%s" % (pred_lastname, random.choice(rand_lastnames))
    else:
        lastname = rand_lastnames[random.randint(i_pred, i_succ)]
    promo = random.randint(100, 999)
    return "%s.%s.%d" % (firstname, lastname, promo)

########################################################################

if __name__ == '__main__':

    # Check arguments
    if len(sys.argv) != 3:
        print "Usage: %s aliases poisonous" % sys.argv[0]
        print ""
        print "Generate the aliases file with:"
        print "$ mysql x4dat > aliases.txt"
        print "SELECT alias FROM aliases WHERE type = 'a_vie';"
        print "^D"
        sys.exit(1)

    # Parse the list of existing aliases and sort it
    firstnames, lastnames, promos = parse_aliases(sys.argv[1])

    # Generate many virtual lastnames and sort the list
    generator = WordGenerator()
    generator.load(lastnames)
    rand_lastnames = [generator.generate() for i in range(100 * len(lastnames))]
    rand_lastnames.sort()

    # For each original, create a new alias
    # alphabetically between this one and the next one
    handle = open(sys.argv[2], 'w')
    lastnames.append('zzzzzzzz') # hack to avoid off-by-one
    for i in range(len(firstnames)):
        handle.write(create_alias(firstnames[i], lastnames[i], lastnames[i + 1], rand_lastnames))
        handle.write('\n')
    handle.close()



