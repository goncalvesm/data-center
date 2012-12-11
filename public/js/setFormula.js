function setFormula(formula) {
	$('#inscription').slideDown(500);
	
	if(formula == '1'){
		document.getElementById('formule').selectedIndex = 0;
	} else if (formula == '10') {
		document.getElementById('formule').selectedIndex = 1;
	} else if (formula == '100') {
		document.getElementById('formule').selectedIndex = 2;
	}
}