function toggleRows(){

    let rows = document.querySelectorAll(".extra-row");
    let btn = document.getElementById("toggleBtn");

    rows.forEach(function(row){

        if(row.style.display === "none"){
            row.style.display = "table-row";
            btn.innerText = "⬆ Show Less";
        }
        else{
            row.style.display = "none";
            btn.innerText = "⬇ Show More";
        }

    });

}