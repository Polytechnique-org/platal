function closePopup() {
  window.close();
}
if (navigator.appName=="Microsoft Internet Explorer") {
  function keyboardIE() { if (event.keyCode == 27) closePopup(); };
  document.onkeydown = keyboardIE;
} else {
  function keyboardOther(e) { if (e.keyCode == 27) closePopup(); };
  document.onkeydown = keyboardOther;
}

