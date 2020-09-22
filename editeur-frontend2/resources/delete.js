function delet(){
  var templateUrl = '<?= get_bloginfo("template_url"); ?>';
  var xhttp= new XMLHttpRequest();
xhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
    const post=document.getElementById("post").value;
    const ppost=document.getElementById("postdeux").value;
    delete ppost;
}else{
console.log(4);
};
xhttp.open("POST","requete.php", true);
xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xhttp.send("post");
}};