<?php


class ExaleadData{

  var $query;
  var $groups = array();
  var $hits = array();
  var $spellings = array();
  var $keywords = array();

  var $nhits = "";
  var $nmatches = "";
  var $estimated = false;
  var $last = "";
  var $start = "";
  var $end = "";

  function ExaleadData(){}

  function setQuery($query){$this->query = $query;}
  function addHit($hit){$this->hits[] = $hit;}
  function addGroup($group){$this->groups[] = $group;}
  function addSpelling($spelling){$this->spellings[] = $spelling;}
  function addKeyword($keyword){$this->keywords[] = $keyword;} 
}

class ExaleadKeyword{
  var $name = "";
  var $display = "";
  var $count = "";
  var $automatic = "";
  var $refine_href = "";
  var $exclude_href = "";
  var $reset_href = "";

  function ExaleadKeyword(){}
  function clear(){
    $name = "";
    $display = "";
    $count = "";
    $automatic = "";
    $refine_href = "";
    $exclude_href = "";
    $reset_href = "";  
  }
}

class ExaleadGroup{

  var $categories = array();
  var $title = "";
  var $clipped = false;
  var $count = "";
  var $browsed = false;
  var $clip_href = "";
  var $reset_href = "";
  
  function ExaleadGroup(){}

  function addCategory($category){
    $this->categories[] = $category;
  }

  function setTitle($title){$this->title = $title;}
  function setClipped($clipped){$this->clipped = $clipped;}
  function setCount($count){$this->count = $count;}
  function setBrowsed($browsed){$this->browsed = $browsed;}
  function setClipHref($clip_href){$this->clip_href = $clip_href;}
  function setResetHref($reset_href){$this->reset_href = $reset_href;}

  function clear(){
   $this->categories = array();
   $this->title = "";
   $this->clipped = false;
   $this->count = "";
   $this->browsed = false;
   $this->clip_href = "";
   $this->reset_href = "";
  }

}

class ExaleadSpelling{

  var $display = "";
  var $query_href = "";

  function ExaleadSpelling(){}
  
  function setDisplay($display){$this->display = $display;}
  function setQueryHref($query_href){$this->query_href = $query_href;}

  function clear(){
    $this->display = "";
    $this->query_href = "";
  }
}

class ExaleadCategory{
  var $name = "";
  var $display = "";
  var $count = "";
  var $automatic = false;
  var $refine_href = "";
  var $exclude_href = "";
  var $reset_href = "";
  var $cref = "";
  var $gid = "";
  var $gcount = "";

  function ExaleadCategory(){}

  function clear(){
   $this->name = "";
   $this->display = "";
   $this->count = "";
   $this->automatic = false;
   $this->refine_href = "";
   $this->exclude_href = "";
   $this->reset_href = "";
   $this->cref = "";
   $this->gid = "";
   $this->gcount = "";
  }
  
}

class ExaleadHit{
  var $hitfields = array();
  var $hitgroups = array();
  var $actions = array();
  var $score = "";
  var $url = "";

  function ExaleadHit(){}

  function addHitField($hitfield){$this->hitfields[] = $hitfield;}
  function addHitGroup($hitgroup){$this->hitgroups[] = $hitgroup;}
  function addAction($action){$this->actions[] = $action;}

  function clear(){
   $this->hitfields = array();
   $this->hitgroups = array();
   $this->actions = array();
   $this->score = "";
   $this->url = "";
  }
}

class ExaleadHitGroup{
  var $hitcategories = array();
  var $title = '';
  var $gid = '';

  function ExaleadHitGroup(){}

  function addHitCategory($hitcategory){$this->hitcategories[] = $hitcategory;}

  function clear(){
    $this->hitcategories = array();
    $this->title = '';
    $this->gid = '';
  }
}

class ExaleadHitCategory{
  var $name = '';
  var $display = '';
  var $cref = '';
  var $gid = '';
  var $browseHref = '';

  function ExaleadHitCategory(){}

  function clear(){
    $this->name = '';
    $this->display = '';
    $this->cref = '';
    $this->gid = '';
    $this->browseHref = '';
  }
}

class ExaleadHitField{
  var $text_segments = array();
  var $has_text_cut = false;
  var $name = "";
  var $value = "";

  function ExaleadHitField(){}

  function addTextSegment($text_segment){$this->text_segments[] = $text_segment;}
  function setHasTextCut($has_text_cut){$this->has_text_cut = $has_text_cut;}
  
  function clear(){
   $this->text_segments = array();
   $this->has_text_cut = false;
   $this->name = "";
   $this->value = "";
  }
}

class ExaleadAction{
  var $display;
  var $kind;
  var $execHref;

  function ExaleadAction(){}

  function clear(){
    $this->display = '';
    $this->kind = '';
    $this->execHref = '';
  }
}

class ExaleadTextSegment{
  var $text = "";
  var $highlighted = false;

  function ExaleadTextSegment(){
  }

  function setHighlighted($highlighted){$this->highlighted = $highlighted;}

  function append($text){
    $this->text .= $text;
  }

  function clear(){
    $this->text = "";
    $this->highlighted = false;
  }
}

class ExaleadQuery{
  var $query_parameters = array();
  var $query_terms = array();
  var $query = "";
  var $context = "";
  var $time = "";
  var $interrupted = false;
  var $browsed = false;
  
  function ExaleadQuery(){}

  function addParameter($parameter){$this->query_parameters[] = $parameter;}
  function addTerm($term){$this->query_terms[] = $term;}

  function clear(){
    $this->query_parameters = array();
    $this->query_terms = array();
    $this->query = "";
    $this->context = "";
    $this->time = "";
    $this->interrupted = false;
    $this->browsed = false;
  }
}

class ExaleadQueryParameter{
 
  var $name = "";
  var $value = "";
 
  function ExaleadQueryParameter(){}

  function clear(){
    $this->name = "";
    $this->value = "";
  }
}

class ExaleadQueryTerm{
 
  var $level = "";
  var $regexp = "";
 
  function ExaleadQueryTerm(){}

  function clear(){
    $this->regexp = "";
    $this->level = "";
  }
}


?>
