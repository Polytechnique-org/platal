#!/usr/bin/env python
# -*- coding: utf-8 -*-
#***************************************************************************
#*  Copyright (C) 2003-2016 Polytechnique.org                              *
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
#***************************************************************************/

"""Helper script to check "encrypted" data returned to the Wats4U SSO endpoint."""

import argparse
import base64

from Crypto.Cipher import Blowfish


def decrypt(data, key):
    """Decrypt a set of base64-encoded data for a given key.

    Args:
        data: str, the base64url-encoded ciphertext
        key: bytes, the key to use
    """
    cipher = Blowfish.new(key, Blowfish.MODE_ECB)
    b64enc = data.replace(b'-', b'+').replace(b'_', b'/').replace(b',', b'=')
    enc = base64.b64decode(b64enc)
    cleartext = cipher.decrypt(enc)
    return cleartext.rstrip(b'0').decode('ascii')


def main():
    parser = argparse.ArgumentParser("Decrypt a Wats4U-encoded ciphertext")
    parser.add_argument('--key', required=True, help="The (ASCII-only) encryption key")
    parser.add_argument('ciphertext', help="The base64url-encoded ciphertext")

    args = parser.parse_args()

    key = args.key.encode('ascii')
    data = args.ciphertext.encode('ascii')

    print(decrypt(data, key))


if __name__ == '__main__':
    main()
