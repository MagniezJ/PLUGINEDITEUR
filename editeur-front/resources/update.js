const sub=document.getElementById('update-post');
const titre=document.getElementById('titre');
console.log("BYE");
sub.addEventListener('click',()=>{
        var xhttp= new XMLHttpRequest(); //event onclick
        var url=myScript.script_directory;
        xhttp.open("POST",url+"/resources/requete2.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        console.log("cool");
        xhttp.onreadystatechange = function() {//ne s'affiche pas
                if (this.readyState == 4 && this.status == 200) {
                console.log("coolREUSSI");
                }
        }
        xhttp.send("id=189&title=test");
});