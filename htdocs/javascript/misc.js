function getNow() {
    dt=new Date();
    dy=dt.getDay();
    mh=dt.getMonth();
    wd=dt.getDate();
    yr=dt.getYear();
    if (yr<1000) yr += 1900;
    hr=dt.getHours();
    mi=dt.getMinutes();
    if (mi<10)
        time=hr+":0"+mi;
    else
        time=hr+":"+mi;
    days=new Array ("Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi");
    months=new Array ("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
    return days[dy]+" "+wd+" "+months[mh]+" "+yr+"<br />"+time;
}

function popup(an) { window.open(an.href); return false; }
