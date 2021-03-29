const resetButton = document.createElement("button")
resetButton.textContent = "Refresh page"
document.body.appendChild(resetButton)

if (isMobileDevice()) {
	document.querySelector("p").innerText = "";
}

const refresh = () => {
	location.reload()
}

resetButton.addEventListener("click", refresh)

const mouseover = function () {
	const element = this.children;
	for (let index = 0; index < element.length; index++) {
		element[index].style.backgroundColor = "yellow";
		element[index].style.fontWeight = "bold";
	}
}
const mouseout = function () {
	const element = this.children;
	for (let index = 0; index < element.length; index++) {
		element[index].style.backgroundColor = "";
		element[index].style.fontWeight = "";
	}
}

const allTr = document.querySelectorAll("tr");
allTr.forEach(element => {
	element.addEventListener("mouseover", mouseover)
	element.addEventListener("mouseout", mouseout)
});

function loadJSON(callback) {
	var xobj = new XMLHttpRequest();
	xobj.overrideMimeType("application/json");
	xobj.open('GET', 'config.json', false);
	xobj.onreadystatechange = function () {
		if (xobj.readyState == 4 && xobj.status == "200") {
			// Required use of an anonymous callback as .open will NOT return a value but simply returns undefined in asynchronous mode
			callback(xobj.responseText);
		}
	};
	xobj.send(null);
}

let configuration = "";

function init() {
	loadJSON(function (response) {
		// Parse JSON string into object
		configuration = JSON.parse(response);
	});
}

const select = document.querySelector("select")

let size = "";

const repleaceBytes = function () {
	const bytesClass = document.querySelectorAll(".bytes");
	bytesClass.forEach(element => {
		// console.log(Math.round(parseInt(element.innerText)/1024/1024/1024));
		// console.log((parseInt(element.innerText) / ‭1073741824));‬
		if (element.innerText == "") {
			//does nothing
		} else if (isMobileDevice()) {
			element.innerText = ((element.dataset.bytes) / 1024 / 1024 / 1024).toFixed(2) + " GiB";
		} else {
			switch (document.querySelector("select").value) {
				case "GiB":
					element.innerText = ((element.dataset.bytes) / 1024 / 1024 / 1024).toFixed(2) + " GiB";
					break;
				case "MiB":
					element.innerText = ((element.dataset.bytes) / 1024 / 1024).toFixed(2) + " MiB";
					break;
				case "kiB":
					element.innerText = ((element.dataset.bytes) / 1024).toFixed(2) + " kiB";
					break;
				case "B":
					element.innerText = element.dataset.bytes + " B";
					break;
				default:
					break;
			}
			//			element.innerText = Math.round(parseInt(element.innerText)/1024/1024/1024) + " GB";
		}
	});
}

init();

const setValueFromConfiguration = () => {
	if (!isMobileDevice()) {
		select.value = configuration.bytes;
		select.id = configuration.bytes;
	}
}
// setTimeout(document.querySelector("select").value=configuration.bytes, 1000);
// readConfig();
// setTimeout(setValueFromConfiguration, 250);
// setTimeout(repleaceBytes, 500);
setValueFromConfiguration()
repleaceBytes()

function changedSelect() {
	configuration.bytes = select.value;
	select.id = select.value;
	repleaceBytes();
}

let intervalID = setInterval(refresh, configuration.time);
let intervalWorking = true;

const switchRefreshement = () => {
	if (intervalWorking) {
		clearInterval(intervalID);
		intervalWorking = false;
	} else {
		intervalID = setInterval(refresh, configuration.time);
		intervalWorking = true;
	}
}

document.querySelector("input").addEventListener("click", switchRefreshement);

function isMobileDevice() {
	return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
};

const ul = document.querySelectorAll("ul");
let dateSwitch = true;
const switchShowingDate = () => {
	if (dateSwitch) {
		ul.forEach(element => {
			element.setAttribute("hidden", null);
		});
		dateSwitch = false;
	} else {
		ul.forEach(element => {
			element.removeAttribute("hidden");
		});
		dateSwitch = true;
	}
}

document.querySelector(".date").addEventListener("click", switchShowingDate);