#!/bin/bash

# Copyright (C) 2015, BMW Car IT GmbH
#
# Permission is hereby granted, free of charge, to any person obtaining a
# copy of this software and associated documentation files (the "Software"),
# to deal in the Software without restriction, including without limitation
# the rights to use, copy, modify, merge, publish, distribute, sublicense,
# and/or sell copies of the Software, and to permit persons to whom the
# Software is furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
# THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
# FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
# DEALINGS IN THE SOFTWARE.

VENDOR_FOLDER=`realpath $1`
PATCHDIR=`realpath $(dirname $0)`

echo "===================================="
echo "Patch KOMENCO dependencies"
echo ""
echo "Vendor folder: $VENDOR_FOLDER"
echo "Patch dir: $PATCHDIR"
echo ""

apply_patch () {
	patch -i $PATCHDIR/$1 \
		-d $VENDOR_FOLDER/$2 -p1
}

# patch dependencies
apply_patch 0001-Handle-custom-sreg-attribute-namespaces.patch \
	opauth/openid

apply_patch 0001-Fix-opauth-parse-uri-function.patch \
	opauth/opauth

apply_patch 0001-Add-method-to-create-remote-links-for-an-issue.patch \
	chobie/jira-api-restclient

apply_patch 0001-Remove-inline-block-from-wrapper-element.patch \
	twitter/typeahead.js

apply_patch 0002-Do-not-overwrite-background-color-of-input-fields.patch \
	twitter/typeahead.js