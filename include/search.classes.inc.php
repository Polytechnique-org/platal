<?php
class SField {
    var $fieldFormName;
    var $fieldDbName;
    var $value;

    function SField($_fieldFormName,$_fieldDbName='') {
        $this->fieldFormName = $_fieldFormName;
        $this->fieldDbName = $_fieldDbName;
        $this->get_request();
    }

    function get_request() {
        $this->value =
        (isset($_REQUEST[$this->fieldFormName]))?trim(stripslashes($_REQUEST[$this->fieldFormName])):'';
    }

    function error($explain) {
        global $page;
        $page->assign('error',$explain);
        $page->run();
    }

    function get_where_statement() {
        return ($this->value!='');
    }

    function get_order_statement() {
        return false;
    }

    function get_url() {
        if ($this->value=='')
            return false;
        else
            return $this->fieldFormName.'='.urlencode($this->value);
    }
}

class NumericSField extends SField {
    function NumericSField($_fieldFormName) {
        $this->fieldFormName = $_fieldFormName;
        $this->get_request();
    }
    
    function get_request() {
        parent::get_request();
        if ($this->value=='')
            $this->value = 0;
        if (!preg_match("/^[0-9]+$/", $this->value))
            $this->error('Un champ numérique contient des caractères alphanumériques.<br>');
    }
}

class StringSField extends SField {
    function get_request() {
        parent::get_request();
        if (preg_match(":[][<>{}~/§_`|%$^=+]|\*\*:", $this->value))
            $this->error('Un champ contient un caractère interdit rendant la recherche'
            .' impossible.<br>');
    }

    function length() {
        return
        length($this->value)-length(ereg_replace('[a-z]'.$CARACTERES_ACCENTUES,'',strtolower($this->value)));
    }

    function get_like($field) {
        //on rend les traits d'union et les espaces équivalents
        $regexp = preg_replace('/[ -]/','[ \-]',$this->value);
        //on remplace le pseudo language des * par une regexp
        $regexp = str_replace('*','.+',$regexp);
        return $field." RLIKE '^(.*[ -])?".replace_accent_regexp($regexp).".*'";
    }

    function get_where_statement() {
        if (!parent::get_where_statement())
            return false;
        return '('.implode(' OR ',array_map(array($this,'get_like'),$this->fieldDbName)).')';
    }

    function get_different($field) {
        return $field.'!="'.$this->value.'"';
    }

    function get_order_statement() {
        if ($this->value!='')
            return implode(',',array_map(array($this,'get_different'),$this->fieldDbName));
        else
            return false;
    }
}

class PromoSField extends SField {
    var $compareField;

    function PromoSField($_fieldFormName,$_compareFieldFormName,$_fieldDbName) {
        parent::SField($_fieldFormName,$_fieldDbName);
        $this->compareField = new SField($_compareFieldFormName);
    }

    function get_request() {
        parent::get_request();
        if (!(empty($this->value) or preg_match("/^[0-9]{4}$/", $this->value)))
            $this->error('La promotion est une année à quatre chiffres.<br>');
    }

    function is_a_single_promo() {
        return ($this->compareField->value=='=' && $this->value!='');
    }

    function get_where_statement() {
        if (!parent::get_where_statement())
            return false;
        return $this->fieldDbName.$this->compareField->value.$this->value;
    }

    function get_url() {
        if (!($u=parent::get_url()))
            return false;
        return $u.'&'.$this->compareField->get_url();
    }
}

class SFieldGroup {
    var $fields;
    var $and;

    function SFieldGroup($_and,$_fields) {
        $this->fields = $_fields;
        $this->and = $_and;
    }

    function field_get_where($f) {
        return $f->get_where_statement();
    }

    function field_get_order($f) {
        return $f->get_order_statement();
    }

    function field_get_url($f) {
        return $f->get_url();
    }

    function get_where_statement() {
        $joinText=($this->and)?' AND ':' OR ';
        return '('.implode($joinText,array_filter(array_map(array($this,'field_get_where'),$this->fields))).')';
    }

    function get_order_statement() {
        $order = array_filter(array_map(array($this,'field_get_order'),$this->fields));
        return (count($order)>0)?implode(',',$order):false;
    }

    function get_url() {
        $url = array_filter(array_map(array($this,'field_get_url'),$this->fields));
        return (count($url)>0)?implode('&',$url):false;
    }
}
?>
