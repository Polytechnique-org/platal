<MultiPg>
<NoDoc>    
<UseLocalTime>
<MsgLocalDateFmt>
%d-%m-%y
</MsgLocalDateFmt>
<IdxSize>
25
</IdxSize>
<NoFolRefs>


<MIMEAltPrefs>
text/plain
text/html
</MIMEAltPrefs>

<MIMEArgs>
m2h_external::filter; forceinline
m2h_text_plain::filter; fancyquote maxwidth=80 quoteclass=quote
</MIMEArgs>

<AttachmentDir>
/home/x2000habouzit/mh/attach/
</AttachmentDir>

<AttachmentURL>
../attach
</AttachmentURL>

<!-- ------------------------------------------------------------------------ -->
<!--                                                                          -->
<!--  VARS                                                                    -->
<!--                                                                          -->
<!-- ------------------------------------------------------------------------ -->

<DefineVar>
MY-LINK
?liste={$$smarty.request.liste}&amp;file=
</DefineVar>

<DefineVar>
MY-ESCSUBJ
$SUBJECTNA$
</DefineVar>

<DefineVar>
MY-SUBJ
<a href="$MY-LINK$$MSG$" id="m$MSGNUM$" title="$MY-ESCSUBJ$">$MY-ESCSUBJ$</a>
</DefineVar>

<DefineVar>
MY-SUBJNA
<strong title="$MY-ESCSUBJ$">$MY-ESCSUBJ$</strong>
</DefineVar>


<!-- ------------------------------------------------------------------------ -->
<!--                                                                          -->
<!--  LINKS                                                                   -->
<!--                                                                          -->
<!-- ------------------------------------------------------------------------ -->

<PrevPgLink>
<a href="$MY-LINK$$PG(PREV)$"><img src="/images/lists_prev.png" alt="précédent" /></a>
</PrevPgLink>

<PrevPgLinkIA>
<img src="/images/lists_previa.png" alt="précedent" />
</PrevPgLinkIA>

<NextPgLink>
<a href="$MY-LINK$$PG(NEXT)$"><img src="/images/lists_next.png" alt="suivant" /></a>
</NextPgLink>

<NextPgLinkIA>
<img src="/images/lists_nextia.png" alt="suivant" />
</NextPgLinkIA>

<FirstPgLink>
<a href="$MY-LINK$$PG(FIRST)$"><img src="/images/lists_first.png" alt="début" /></a>
</FirstPgLink>

<LastPgLink>
<a href="$MY-LINK$$PG(LAST)$"><img src="/images/lists_last.png" alt="fin" /></a>
</LastPgLink>


<TPrevPgLink>
<a href="$MY-LINK$$PG(TPREV)$"><img src="/images/lists_prev.png" alt="précédent" /></a>
</TPrevPgLink>

<TPrevPgLinkIA>
<img src="/images/lists_previa.png" alt="précedent" />
</TPrevPgLinkIA>

<TNextPgLink>
<a href="$MY-LINK$$PG(TNEXT)$"><img src="/images/lists_next.png" alt="suivant" /></a>
</TNextPgLink>

<TNextPgLinkIA>
<img src="/images/lists_nextia.png" alt="suivant" />
</TNextPgLinkIA>

<TFirstPgLink>
<a href="$MY-LINK$$PG(TFIRST)$"><img src="/images/lists_first.png" alt="début" /></a>
</TFirstPgLink>

<TLastPgLink>
<a href="$MY-LINK$$PG(TLAST)$"><img src="/images/lists_last.png" alt="fin" /></a>
</TLastPgLink>


<TPrevInButton>
<a href="$MY-LINK$$MSG(TPREVIN)$"><img src="/images/lists_prev.png" alt="précédent" /></a>
</TPrevInButton>

<TPrevInButtonIA>
<img src="/images/lists_previa.png" />
</TPrevInButtonIA>

<TNextInButton>
<a href="$MY-LINK$$MSG(TNEXTIN)$"><img src="/images/lists_next.png" alt="suivant" /></a>
</TNextInButton>

<TNextInButtonIA>
<img src="/images/lists_nextia.png" alt="suivant" />
</TNextInButtonIA>

<TPrevTopButton>
<a href="$MY-LINK$$MSG(TPREVTOP)$"><img src="/images/lists_first.png" alt="début" /></a>
</TPrevTopButton>

<TNextTopButton>
<a href="$MY-LINK$$MSG(TNEXTTOP)$"><img src="/images/lists_last.png" alt="fin" /></a>
</TNextTopButton>

<TPrevTopButtonIA>
<img src="/images/lists_firstia.png" alt="début" />
</TPrevTopButtonIA>

<TNextTopButtonIA>
<img src="/images/lists_lastia.png" alt="fin" />
</TNextTopButtonIA>

<!-- ------------------------------------------------------------------------ -->
<!--                                                                          -->
<!--  MESSAGE INDEX                                                           -->
<!--                                                                          -->
<!-- ------------------------------------------------------------------------ -->

<MsgPgSSMarkup>

</MsgPgSSMarkup>

<MsgPgBegin>

</MsgPgBegin>

<MsgPgEnd>

</MsgPgEnd>

<TopLinks>
    <h1>Vue de message</h1>
    <table class='bicol' cellpadding="0" cellspacing="0">
    <tr>
      <th>fil&nbsp;précédent</th>
      <th>msg.&nbsp;précédent</th>
      <th>vue&nbsp;par&nbsp;dates/fils</th>
      <th>msg.&nbsp;suivant</th>
      <th>fil&nbsp;suivant</th>
    </tr>
    <tr class="impair">
      <td class="center">$BUTTON(TPREVTOP)$</td>
      <td class="center">$BUTTON(TPREVIN)$</td>
      <td class="center">
        <a href="$MY-LINK$$IDXFNAME$#m$MSGNUM$"><img src="/images/lists_date.png" alt="par dates" /></a>
        /
        <a href="$MY-LINK$$TIDXFNAME$#m$MSGNUM$"><img src="/images/lists_thread.png" alt="par fils" /></a>
      </td>
      <td class="center">$BUTTON(TNEXTIN)$</td>
      <td class="center">$BUTTON(TNEXTTOP)$</td>
    </tr>
    </table>
</TopLinks>

<SubjectHeader>

</SubjectHeader>

<FieldsBeg>
    <table class="bicol" cellpadding="0" cellspacing="0">
</FieldsBeg>
 
<LabelBeg>
      <tr>
	<td class="right">
</LabelBeg>
<LabelStyles>
-default-:
</LabelStyles>
<LabelEnd>
	</td>
</LabelEnd>
   
<FldBeg>
	<td>
</FldBeg>
<FieldOrder>
subject
from
to
date
</FieldOrder>
<FieldStyles>
subject:strong
-default-:
</FieldStyles>
<FldEnd>
        </td>
      </tr>
</FldEnd>
     
<FieldsEnd>
    </table>
</FieldsEnd>

<HeadBodySep>
    <table class="bicol" cellpadding="0" cellspacing="0">
    <tr class="pair">
      <td>{literal}
</HeadBodySep>

<MsgBodyEnd>
        {/literal}</td>
      </tr>
    </table>
    $TSLICE$
</MsgBodyEnd>

<BotLinks>

</BotLinks>

<MsgFoot>
</MsgFoot>

<!-- ------------------------------------------------------------------------ -->
<!--                                                                          -->
<!--  THREAD INDEX                                                            -->
<!--                                                                          -->
<!-- ------------------------------------------------------------------------ -->

<TIdxPgSSMarkup>
    
</TIdxPgSSMarkup>

<TIdxPgBegin>

</TIdxPgBegin>

<TIdxPgEnd>

</TIdxPgEnd>

<THead>
    <h1>Archives de la liste {$$smarty.request.liste} ($PAGENUM$/$NUMOFPAGES$)</h1>
    <table class='bicol' cellpadding="0" cellspacing="0">
    <tr>
      <th>&nbsp;</th>
      <th>précédent</th>
      <th>vue&nbsp;par&nbsp;date</th>
      <th>suivant</th>
      <th>&nbsp;</th>
    </tr>
    <tr class="impair">
      <td class="center">$PGLINK(TFIRST)$</td>
      <td class="center">$PGLINK(TPREV)$</td>
      <td class="center"><a href="$MY-LINK$$IDXFNAME$"><img src="/images/lists_date.png" alt="par dates" /></a></td>
      <td class="center">$PGLINK(TNEXT)$</td>
      <td class="center">$PGLINK(TLAST)$</td>
    </tr>
    </table>
    <table class="bicol" cellspacing="0" cellpadding="0">
</THead>

<TFoot>
    </table>
</TFoot>

<!-- ------------------------------------------------------------------------ -->

<TIndentBegin>

</TIndentBegin>
<TIndentEnd>

</TIndentEnd>

<!-- ------------------------------------------------------------------------ -->

<TTopBegin>
<tr class="pair"><td colspan="3" class="center"> - - - </td></tr>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJ$</td>
  <td class='right'><em>$FROMNAME$</em></td>
</tr>
</TTopBegin>
<TTopEnd>

</TTopEnd>

<TContBegin>
<tr>
  <td>&nbsp;</td>
  <td style="padding-left: $TLEVEL$em"><em>[...]</em></td>
  <td>&nbsp;</td>
</tr>
</TContBegin>
<TContEnd>

</TContEnd>

<!-- ------------------------------------------------------------------------ -->

<TSublistBeg>

</TSublistBeg>
<TSublistEnd>

</TSublistEnd>


<TSubjectBeg>

</TSubjectBeg>
<TSubjectEnd>

</TSubjectEnd>

<!-- ------------------------------------------------------------------------ -->

<TSingleTxt>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJ$</td>
  <td class='right'><em>$FROMNAME$</em></td>
</tr>
</TSingleTxt>

<TLiTxt>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJ$</td>
  <td class='right'><em>$FROMNAME$</em></td>
</tr>
</TLiTxt>
<TLiEnd>

</TLiEnd>

<TLiNone>
<tr>
  <td style="padding-left: $TLEVEL$em"><em>&lt;non disponnible&gt;</em></td>
  <td>&nbsp;</td>
</tr>
</TLiNone>


<!-- ------------------------------------------------------------------------ -->

<TSlice>
10;10;1
</TSlice>

<TSliceBeg>
<table class="bicol" cellpadding="0" cellspacing="0">
</TSliceBeg>
<TSliceEnd>
</table>
</TSliceEnd>

<TSliceTopBegin>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJ$</td>
  <td class='right'><em>$FROMNAME$</em></td>
</tr>
</TSliceTopBegin>

<TSliceTopBeginCur>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJNA$</td>
  <td class='right'><strong><em>$FROMNAME$</em></strong></td>
</tr>
</TSliceTopBeginCur>
<TSliceTopEndCur>

</TSliceTopEndCur>


<TSliceLiTxtCur>
<tr>
  <td>$MSGLOCALDATE$</td>
  <td class='subj' style="padding-left: $TLEVEL$em">$MY-SUBJNA$</td>
  <td class='right'><strong><em>$FROMNAME$</em></strong></td>
</tr>
</TSliceLiTxtCur>
<TSliceLiEndCur>

</TSliceLiEndCur>

<!-- ------------------------------------------------------------------------ -->
<!--                                                                          -->
<!--  DATE INDEX                                                              -->
<!--                                                                          -->
<!-- ------------------------------------------------------------------------ -->

<Sort>
<IdxFname>
dates.html
</IdxFname>
<IdxPrefix>
date
</IdxPrefix>


<IdxPgSSMarkup>
    
</IdxPgSSMarkup>

<IdxPgBegin>

</IdxPgBegin>

<IdxPgEnd>

</IdxPgEnd>

<ListBegin>
    <h1>Archives de la liste {$$smarty.request.liste} ($PAGENUM$/$NUMOFPAGES$)</h1>
    <table class='bicol' cellpadding="0" cellspacing="0">
    <tr>
      <th>&nbsp;</th>
      <th>précédent</th>
      <th>vue&nbsp;par&nbsp;fils</th>
      <th>suivant</th>
      <th>&nbsp;</th>
    </tr>
    <tr class="impair">
      <td class="center">$PGLINK(FIRST)$</td>
      <td class="center">$PGLINK(PREV)$</td>
      <td class="center"><a href="$MY-LINK$$TIDXFNAME$"><img src="/images/lists_thread.png" alt="par fils" /></a></td>
      <td class="center">$PGLINK(NEXT)$</td>
      <td class="center">$PGLINK(LAST)$</td>
    </tr>
    </table>
    <table class="bicol" cellpadding="0" cellspacing="0">
</ListBegin>

<ListEnd>
    </table>
</ListEnd>

<!-- ------------------------------------------------------------------------ -->
<DayBegin>
</DayBegin>

<DayEnd>
</DayEnd>

<LITemplate>
      <tr>
	<td>$MSGLOCALDATE$</td>
	<td class='subj'>$MY-SUBJ$</td>
	<td class='right'><em>$FROMNAME$</em></td>
      </tr>
</LITemplate>


<!-- vim:set syntax=mhonarc:sw=2: -->

<!-- ------------------------------------------------------------------------ -->
<!-- vim:set syntax=mhonarc:sw=2: -->
