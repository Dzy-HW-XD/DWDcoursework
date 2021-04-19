var navdiv = document.getElementById("nav-div");
var section = document.getElementById("section");
var section2 = document.getElementById("section2");
var spanbutton = document.getElementById("spanbutton");

navdiv.onclick = function() {
    section.style.display = 'none';
    section2.style.display = 'block';
}