<?php

// vim:set et sw=4 sts=4 sws=4 foldmethod=marker:
    class Ancien {
        var $NOM_PATR;
        var $PART_NOM;
        var $PRENOM;
        var $NOM_USUEL;
        var $PART_NOMUS;
        var $NOM_COMPLET;
        var $CIVILITE;
        var $GRADE;
        var $CORPS;
        var $PROMO;
        var $NATIONALITE;
        var $CORPS_LIBELLE;
        var $EMAIL;
        var $TYPE_ANC;
        var $ADRESSE = array();
        var $NUM_ADRESSE = 0;
        var $ACTIVITE = array();
        var $NUM_ACTIVITE = 0;
        var $FORMATION = array();
        var $NUM_FORMATION = 0;
        var $Erreur;

    function Ancien ($aa) {
       if ($aa<>'')
       {
        foreach ($aa as $k=>$v)
           $this->$k = $aa[$k];
       }
    }
    function Ajout_adresse ($adresse,$i) {
           $this->ADRESSE[$i] = $adresse;
           $this->NUM_ADRESSE++;
    }
    function Ajout_activite ($activite,$i) {
           $this->ACTIVITE[$i] = $activite;
           $this->NUM_ACTIVITE++;
    }
    function Ajout_formation ($formation,$i) {
           $this->FORMATION[$i] = $formation;
           $this->NUM_FORMATION++;
    }
        function Ajout_erreur ($erreur) {
           $this->Erreur = $erreur;
    }
    function Erreur() {
        return $this->Erreur;
    }
    function Nom_patr() {
        return $this->NOM_PATR;
    }
    function Part_nom() {
        return $this->PART_NOM;
    }
    function Prenom() {
        return $this->PRENOM;
    }
    function Nom_usuel() {
        return $this->NOM_USUEL;
    }
    function Part_nomus() {
        return $this->PART_NOMUS;
    }
    function Nom_complet() {
        return $this->NOM_COMPLET;
    }
    function Civilite() {
        return $this->CIVILITE;
    }
    function Grade() {
        return $this->GRADE;
    }
    function Corps() {
        return $this->CORPS;
    }
    function Corps_Libelle() {
        return $this->CORPS_LIBELLE;
    }
    function Promo() {
        return $this->PROMO;
    }
    function Nationalite() {
        return $this->NATIONALITE;
    }
    function Email() {
        return $this->EMAIL;
    }
    function Type_anc() {
        return $this->TYPE_ANC;
    }
    function Adresse_type($i) {
        return $this->ADRESSE[$i]->TYPE;
    }
    function Adresse1($i) {
        return $this->ADRESSE[$i]->ADRESS1;
    }
    function Adresse2($i) {
        return $this->ADRESSE[$i]->ADRESS2;
    }
    function Adresse3($i) {
        return $this->ADRESSE[$i]->ADRESS3;
    }
    function Code_pst($i) {
        return $this->ADRESSE[$i]->CODE_PST;
    }
    function Ville($i) {
        return $this->ADRESSE[$i]->VILLE;
    }
    function Zip_cedex($i) {
        return $this->ADRESSE[$i]->ZIPCEDEX;
    }
    function Etat_region($i) {
        return $this->ADRESSE[$i]->ETATREGION;
    }
    function Pays($i) {
        return $this->ADRESSE[$i]->PAYS;
    }
    function Mobile($i) {
        return $this->ADRESSE[$i]->MOBILE;
    }
    function Tel($i) {
        return $this->ADRESSE[$i]->TEL;
    }
    function Fax($i) {
        return $this->ADRESSE[$i]->FAX;
    }
    function Num_adresse() {
        return $this->NUM_ADRESSE;
    }
    function Entreprise($i) {
        return $this->ACTIVITE[$i]->ENTREPRISE;;
    }
    function Raison_sociale($i) {
        return $this->ACTIVITE[$i]->RAISON_SOCIALE;
    }
    function Fonction($i) {
        return $this->ACTIVITE[$i]->FONCTION;
    }
    function Adresse_act_type($i) {
        return $this->ACTIVITE[$i]->TYPE;
    }
    function Adresse_act_adresse1($i) {
        return $this->ACTIVITE[$i]->ADRESS1;
    }
    function Adresse_act_adresse2($i) {
        return $this->ACTIVITE[$i]->ADRESS2;
    }
    function Adresse_act_adresse3($i) {
        return $this->ACTIVITE[$i]->ADRESS3;
    }
    function Adresse_act_code_pst($i) {
        return $this->ACTIVITE[$i]->CODE_PST;
    }
    function Adresse_act_ville($i) {
        return $this->ACTIVITE[$i]->VILLE;
    }
    function Adresse_act_zip_cedex($i) {
        return $this->ACTIVITE[$i]->ZIPCEDEX;
    }
    function Adresse_act_etat_region($i) {
        return $this->ACTIVITE[$i]->ETATREGION;
    }
    function Adresse_act_pays($i) {
        return $this->ACTIVITE[$i]->PAYS;
    }
    function Adresse_act_mobile($i) {
        return $this->ACTIVITE[$i]->MOBILE;
    }
    function Adresse_act_tel($i) {
        return $this->ACTIVITE[$i]->TEL;
    }
    function Adresse_act_fax($i) {
        return $this->ACTIVITE[$i]->FAX;
    }
    function Num_activite() {
        return $this->NUM_ACTIVITE;
    }
    function Formation($i) {
        return $this->FORMATION[$i]->LIBELLE;
    }
    function Num_formation() {
        return $this->NUM_FORMATION;
    }
    }

    class Adresse {
        var $TYPE;
        var $ADRESS1;
        var $ADRESS2;
        var $ADRESS3;
        var $CODE_PST;
        var $VILLE;
        var $ZIPCEDEX;
        var $ETATREGION;
        var $PAYS;
        var $MOBILE;
        var $TEL;
        var $FAX;

    function Adresse ($aa) {
       foreach ($aa as $k=>$v)
           $this->$k = $aa[$k];
    }
  }

    class Activite {
        var $ENTREPRISE;
        var $RAISON_SOCIALE;
        var $FONCTION;
        var $TYPE;
        var $ADRESS1;
        var $ADRESS2;
        var $ADRESS3;
        var $CODE_PST;
        var $VILLE;
        var $ZIPCEDEX;
        var $ETATREGION;
        var $PAYS;
        var $MOBILE;
        var $TEL;
        var $FAX;

    function Activite ($aa) {
       foreach ($aa as $k=>$v)
           $this->$k = $aa[$k];
    }
  }

    class Formation {
        var $LIBELLE;

    function Formation ($aa) {
       foreach ($aa as $k=>$v)
           $this->$k = $aa[$k];
    }
  }

    function xml_get_children($vals, &$i) {
        $children = array();
        if (isset($vals[$i]['value'])) $children[] = $vals[$i]['value'];

        while (++$i < count($vals)) {
       switch ($vals[$i]['type']) {
       case 'cdata':
           $children[] = $vals[$i]['value'];
           break;

       case 'complete':
           $children[] = array(
               'tag'        => $vals[$i]['tag'],
               'attributes' => isset($vals[$i]['attributes'])?
                               $vals[$i]['attributes'] : null,
               'value'      => $vals[$i]['value'],
           );
           break;

       case 'open':
           $children[] = array(
               'tag'        => $vals[$i]['tag'],
               'attributes' => isset($vals[$i]['attributes'])?
                               $vals[$i]['attributes'] : null,
               'children'  => xml_get_children($vals, $i),
           );
           break;

       case 'close':
           return $children;
       }
       }
    }

    //fonction principale de récupération des données de l'ancien
    //à indiquer: matricule de l'ancien
    function recupere_infos_ancien($AX_MATRICULE)
    {
        include('config.inc');

        $id =14;//14 params persos ancien
        $id_ad = 11; //12 params adresse
        $id_ac = 14; //15 params adresse
        $id_fo = 1; //1 params fonction

        $erreur = "";

        // Génération d'un identifiant de "session" inter-serveurs
        $id_session = md5 (uniqid (rand()));

        // Connexion au serveur pour lui fournir l'identifiant et lui demander un challenge
        $challenge = file_get_contents ($nom_script_renvoi_challenge."?id_session=".urlencode($id_session));
        if ($challenge == 'ERROR')
        {
            $erreur = "Erreur lors de la demande du challenge";
        }
        else
        {
          // On va relire la clé privée (codée)
          $privkeypass = $phrase;
          $pkey_txt = file_get_contents ($chemin_cle_privee);
          if (($pkey_res = openssl_pkey_get_private($pkey_txt, $privkeypass)) == false)
          {
            $erreur = "Erreur lors de l'ouverture de la clé privée";
          }
          else
          {
            // Signature du challenge (cryptage avec clé privée)
            if ( openssl_private_encrypt($challenge, $response, $pkey_res) == false )
            {
             $erreur = "Erreur lors du cryptage du challenge avec la clé privée";
            }
            else
            {
              // Signature du login (cryptage avec clé privée)
              if ( openssl_private_encrypt($AX_MATRICULE, $AX_MATRICULE_CRYPTE, $pkey_res) == false )
              {
                 $erreur = "Erreur lors du cryptage du matricule avec la clé privée";
              }
              else
              {
                    // Envoi de la réponse au serveur avec l'identifiant de l'ancien dont on veut récupérer les infos
                    $reponse_serveur .= file_get_contents ($nom_script_renvoi_infos."?id_session=".urlencode($id_session)."&AX_MATRICULE_CRYPTE=".urlencode($AX_MATRICULE_CRYPTE)."&response=".urlencode($response));

                    $parser = xml_parser_create();
                    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,  1);
                    xml_parse_into_struct($parser, $reponse_serveur, $vals, $index);
                    xml_parser_free($parser);

                    $tree = array();
                    $tree[] = array(
                       'tag'        => $vals[0]['tag'],
                     'attributes' => isset($vals[0]['attributes'])?
                                       $vals[0]['attributes'] : null,
                    'children'  => xml_get_children($vals, $i = 0),
                     );

                    for ($i=0; $i < $id; $i++)
                    {
                        $val_ancien[$tree[0]['children'][0]['children'][$i]['tag']] = $tree[0]['children'][0]['children'][$i]['value'];
                    }

                   $Ancien_renvoye = new Ancien($val_ancien);

                    //adresse while pour nbre d'adresses
                    $num_ad = 0;
                    while ($tree[0]['children'][0]['children'][$id]['tag'] == 'ADRESSE')
                    {
                        for ($i=0; $i <= $id_ad; $i++)
                        {
                            $val_adresse[$tree[0]['children'][0]['children'][$id]['children'][$i]['tag']] = $tree[0]['children'][0]['children'][$id]['children'][$i]['value'];
                        }

                       $Adresse_renvoyee = new Adresse($val_adresse);
                       $Ancien_renvoye->Ajout_adresse($Adresse_renvoyee,$num_ad);

                        //prochaine adresse
                        $num_ad = $num_ad + 1;
                        $id = $id+1;
                    }

                    //idem pour activité
                    $num_ac = 0;
                    while ($tree[0]['children'][0]['children'][$id]['tag'] == 'ACTIVITE')
                    {
                        for ($i=0; $i <= $id_ac; $i++)
                        {
                            $val_activite[$tree[0]['children'][0]['children'][$id]['children'][$i]['tag']] = $tree[0]['children'][0]['children'][$id]['children'][$i]['value'];

                            for ($j=0; $j <= $id_ad; $j++)
                            {
                                $val_activite[$tree[0]['children'][0]['children'][$id]['children'][$i]['children'][$j]['tag']] = $tree[0]['children'][0]['children'][$id]['children'][$i]['children'][$j]['value'];
                            }
                        }

                       $Activite_renvoyee = new Activite($val_activite);
                       $Ancien_renvoye->Ajout_activite($Activite_renvoyee,$num_ac);

                        //prochaine activité
                        $num_ac = $num_ac + 1;
                        $id = $id+1;
                    }
                    //idem pour formation
                    $num_fo = 0;
                    while ($tree[0]['children'][0]['children'][$id]['tag'] == 'FORMATION')
                    {
                        for ($i=0; $i <= $id_fo; $i++)
                        {
                            $val_formation[$tree[0]['children'][0]['children'][$id]['children'][$i]['tag']] = $tree[0]['children'][0]['children'][$id]['children'][$i]['value'];
                        }

                       $Formation_renvoyee = new Formation($val_formation);
                       $Ancien_renvoye->Ajout_formation($Formation_renvoyee,$num_fo);

                        //prochaine formation
                        $num_fo = $num_fo + 1;
                        $id = $id+1;
                    }
              }
            }
          }
        }

        //erreur indiquée
        if ($erreur)  //erreur ici
        {
            $Ancien_renvoye = new Ancien('');
            $Ancien_renvoye->Ajout_erreur($erreur);
        }
        else
        { //renvoyée par le fichier serveur_renvoie_infos
            $Ancien_renvoye->Ajout_erreur($tree[0]['children'][1]['value']);
        }

        return $Ancien_renvoye;
    }

?>
