<?php
/***************************************************************************
 *  Copyright (C) 2003-2013 Polytechnique.org                              *
 *  http://opensource.polytechnique.org/                                   *
 *                                                                         *
 *  This program is free software; you can redistribute it and/or modify   *
 *  it under the terms of the GNU General Public License as published by   *
 *  the Free Software Foundation; either version 2 of the License, or      *
 *  (at your option) any later version.                                    *
 *                                                                         *
 *  This program is distributed in the hope that it will be useful,        *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
 *  GNU General Public License for more details.                           *
 *                                                                         *
 *  You should have received a copy of the GNU General Public License      *
 *  along with this program; if not, write to the Free Software            *
 *  Foundation, Inc.,                                                      *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
 ***************************************************************************/


class SharingAPIModule extends PlModule
{
    function handlers()
    {
        return array(
            'api/1/sharing/search'    => $this->make_api_hook('search',    AUTH_COOKIE, 'api_user_readonly'),
            'api/1/sharing/bulkmail'  => $this->make_api_hook('bulkmail',  AUTH_COOKIE, 'api_user_readonly'),
            'api/1/sharing/picture'   => $this->make_hook('picture_token', AUTH_PUBLIC),
        );
    }

    function handler_search(PlPage $page, PlUser $authUser, $payload)
    {
        require_once 'partnersharing.inc.php';
        $partner = PartnerSharing::fetchByAPIUser($authUser);
        if ($partner == null || !$partner->has_directory) {
            return PL_FORBIDDEN;
        }

        $this->load('request.inc.php');

        $payload = new PlDict($payload);

        $errors = WSDirectoryRequest::validatePayload($payload);

        if (count($errors)) {
            foreach ($errors as $error_code) {
                $page->trigError(WSDirectoryRequest::$ERROR_MESSAGES[$error_code]);
            }
            return PL_BAD_REQUEST;
        }

        // Processing
        $request = new WSDirectoryRequest($partner, $payload);
        $request->assignToPage($page);
        return PL_JSON;
    }

    function handler_bulkmail(PlPage $page, PlUser $authUser, $payload)
    {
        require_once 'partnersharing.inc.php';
        $partner = PartnerSharing::fetchByAPIUser($authUser);
        if ($partner == null || !$partner->has_bulkmail) {
            return PL_FORBIDDEN;
        }

        if (!isset($payload['uids'])) {
            $page->trigError('Malformed query.');
            return PL_BAD_REQUEST;
        }

        $uids = $payload['uids'];

        $pf = new UserFilter(
            new PFC_And(
                new UFC_PartnerSharingID($partner->id, $uids),
                new UFC_HasValidEmail(),
                new UFC_PartnerSharingEmail($partner->id)
            ));

        $contexts = array();
        foreach ($pf->iterUsers() as $user) {
            $contexts[] = array(
                'name' => $user->fullName(),
                'email' => $user->bestEmail(),
                'gender' => $user->isFemale() ? 'woman' : 'man',
            );
        }
        $page->jsonAssign('contexts', $contexts);
        return PL_JSON;
    }

    function handler_picture_token(PlPage $page, $size, $token)
    {
        XDB::rawExecute('DELETE FROM  profile_photo_tokens
                               WHERE  expires <= NOW()');
        $pid = XDB::fetchOneCell('SELECT  pid
                                    FROM  profile_photo_tokens
                                   WHERE  token = {?}', $token);
        if ($pid != null) {
            $res = XDB::fetchOneAssoc('SELECT  attach, attachmime, x, y, last_update
                                         FROM  profile_photos
                                        WHERE  pid = {?}', $pid);
            $photo = PlImage::fromData($res['attach'], 'image/' . $res['attachmime'], $res['x'], $res['y'], $res['last_update']);
            $photo->send();
        } else {
            return PL_NOT_FOUND;
        }
    }
}

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker enc=utf-8:
?>
