<?php

/** décrit un tracker
 * Cette classe décrit un tracker
 * les mails d'administrations envoyés, sont marqués par X-TrackerName: TrackerAdmin
 */
class Tracker {
    /** l'id du tracker */
    var $id;
    /** la description du tracker */
    var $desc;
    /** le nom du tracker */
    var $name;
    /** la mailing list(id) */
    var $ml_id;
    /** la mailing list @poly.org */
    var $ml_short;
    /** nom de la ML */
    var $ml_text;
    /** le niveau nécessaire pour utiliser le tracker ("admin","auth") */
    var $perms;
    /** propriétés du tracker
     * no_mail, perso
     */
    var $bits;
    /** table texte de la priorité */
    var $pris;

    /** constructeur
     * @param	$tr_id	    id du tracker
     */
    function Tracker($tr_id) {
        global $globals;
        $this->id = $tr_id;

        $req = $globals->db->query("SELECT t.texte,t.description,t.perms,t.bits,
                t.pri1,t.pri2,t.pri3,t.pri4,t.pri5,
                m.ml_id,m.short,m.texte
                FROM      trackers.trackers   AS t
                LEFT JOIN trackers.mail_lists AS m USING(ml_id)
                WHERE tr_id='$tr_id'");
        if(!mysql_num_rows($req)) {
            unset($this->id);
            return;
        }
        $this->pris = array ();

        list($this->name,$this->desc,$this->perms,$tr_bits,
                $this->pris[1],$this->pris[2],$this->pris[3],$this->pris[4],$this->pris[5],
                $this->ml_id,$this->ml_short, $this->ml_text) = mysql_fetch_row($req);
        mysql_free_result($req);

        $this->bits = new flagset($tr_bits);
    }

    /** pseudo destructeur
     * détruit toutes les références au tracker et le tracker lui même dans les bases tr_*.
     * notifie tracker@polytechnique.org
     */
    function destroy() {
        mysql_query("DELETE FROM trackers.trackers WHERE tr_id='{$this->id}'");
        mysql_query("DELETE FROM trackers.mails    WHERE tr_id='{$this->id}'");
        mysql_query("DELETE trackers.followups
                     FROM trackers.followups AS f,trackers.requests AS r
                     WHERE r.rq_id=f.rq_id AND r.tr_id='{$this->id}'");
    }

    function post($sujet, $text, $prio, $status) {
        mysql_query("INSERT INTO trackers.requests
                     SET    tr_id='{$this->id}',user_id='{$_SESSION['uid']}',
                            admin_id='-1',st_id='$status',pri='$prio',
                            summary='$sujet',texte='$text'");
        $id = mysql_insert_id();
        # TODO : mail
        return $id;
    }

    function read_perms_ok() {
        if(has_perms())
            return true;
        if(logged() && $this->perms == 'auth')
            return true;
        if($this->perms=="public")
            return true;
        return false;
    }
    
    function post_perms_ok() {
        if(has_perms())
            return true;
        if(identified() && $this->perms == 'auth')
            return true;
        if($this->perms=="public")
            return true;
        return false;
    }
}

function tracker_clean_bd() {
    global $globals;
    $req = $globals->db->query("SELECT ml.ml_id
                                FROM      trackers.mail_lists AS ml
                                LEFT JOIN trackers.trackers   AS tr USING(ml_id)
                                WHERE tr.tr_id is null");
    if(mysql_num_rows($req)) {
        $ids = Array();
        while(list($id) = mysql_fetch_row($req)) $ids[] = $id;
        $globals->db->query("DELETE FROM trackers.mail_lists
                             WHERE ml_id IN (".implode(",",$ids).") AND texte!='null@polytechnique.org'");
    }
    mysql_free_result($req);
}


function tracker_update($name,$desc,$perms,$ml_id,$bits,$pris, $tr_id=0) {
    global $globals;
    if($tr_id>0) {
        $globals->db->query("UPDATE trackers.trackers 
                             SET    perms='$perms',ml_id='$ml_id',texte='$name',description='$desc',bits='{$bits->value}',
                                    pri1='{$pris[1]}',pri2='{$pris[2]}',pri3='{$pris[3]}',pri4='{$pris[4]}',pri5='{$pris[5]}'
                                    WHERE tr_id='$tr_id'");
        return $tr_id;
    } else {
        $globals->db->query("INSERT INTO trackers.trackers 
                             SET perms='$perms',ml_id='$ml_id',texte='$name',description='$desc',bits='{$bits->value}',
                                 pri1='{$pris[1]}',pri2='{$pris[2]}',pri3='{$pris[3]}',pri4='{$pris[4]}',pri5='{$pris[5]}'");
        return mysql_insert_id();
    }
}


function request_delete($tr_id, $rq_id) {
    mysql_query("DELETE FROM trackers.requests WHERE rq_id='$rq_id' AND tr_id='$tr_id'");
    if(mysql_affected_rows()) {
        mysql_query("DELETE FROM trackers.followups WHERE rq_id='$rq_id' AND tr_id='$tr_id'");
# TODO mail
    }
}

?>
