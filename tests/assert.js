/**
 * TesTee
 *
 * A simple testing framework to run js tests within phantomjs without depending
 * on any specific webserver.
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 */

var AssertException = function(message){
	this.message = message;
};

var AssertWaitFor = {
	timeout: 12000, // timeout after 12 seconds
	wait: 100, // how often to check the query in ms

	count: 0,
	query: null,
	callback: null,
	interval: null
};

var Assert = {
	SUCCESS: 0x1,
	FAILURE: 0x2,
	NEXT: 0x3,

	addResult: function(result){
		window.callPhantom(result);
	},

	triggerNext: function(){
		this.addResult({
			code: this.NEXT
		});
	},

	addSuccess: function(){
		this.addResult({
			code: this.SUCCESS,
		});
	},

	addFailure: function(message){
		this.addResult({
			code: this.FAILURE,
			message: message
		});
	},

	/**
	 * At the moment we have no MutationObserver in phantomjs therefor we use
	 * this hack to wait for an element to occur. If phantomjs updates the 
	 * webkit version we can change here the code without changing the tests
	 */
	waitFor: function(selector, callback){
		if (AssertWaitFor.query === null) {
			AssertWaitFor.query = selector;
			AssertWaitFor.callback = callback;
			AssertWaitFor.interval = setInterval(function(){
				var el = document.body.querySelector(AssertWaitFor.query);
				if (el !== null) {
					var callback = AssertWaitFor.callback;

					// clear
					Assert.clearWaitFor();

					// call callback
					callback();
				} else {
					AssertWaitFor.count++;
					// check timeout
					if (AssertWaitFor.count * AssertWaitFor.wait > AssertWaitFor.timeout) {
						var query = AssertWaitFor.query;

						// clear
						Assert.clearWaitFor();

						throw ('Timeout selector "' + query + '" does not occur after ' + (AssertWaitFor.timeout / 1000) + ' seconds');
					}
				}
			}, AssertWaitFor.wait);
		} else {
			throw ('Wait for selector "' + AssertWaitFor.query + '" to occur');
		}
	},

	clearWaitFor: function(){
		// clear interval
		clearInterval(AssertWaitFor.interval);

		// reset
		AssertWaitFor.count = 0;
		AssertWaitFor.query = null;
		AssertWaitFor.callback = null;
		AssertWaitFor.interval = null;
	}

};

Assert.contains = function(expectedValue, array){
	var found = false;
	for (var i = 0; i < array.length; i++) {
		if (array[i] == expectedValue) {
			found = true;
			break;
		}
	}
	if (!found) {
		throw ('Array does not contain "' + expectedValue + '"');
	} else {
		this.addSuccess();
	}
};

Assert.count = function(expectedCount, array){
	if (array.length != expectedCount) {
		throw ('Expect count "' + expectedCount + '" actual "' + array.length + '"');
	} else {
		this.addSuccess();
	}
};

Assert.empty = function(actual){
	if (actual != '') {
		throw ('Expect "' + actual + '" to be empty');
	} else {
		this.addSuccess();
	}
};

Assert.notEmpty = function(actual){
	if (actual == '') {
		throw ('Expect "' + actual + '" to be not empty');
	} else {
		this.addSuccess();
	}
};

Assert.equals = function(expected, actual){
	if (expected != actual) {
		throw ('Expected "' + expected + '" is not equal "' + actual + '"');
	} else {
		this.addSuccess();
	}
};

Assert.equalsJsonStructure = function(expected, actual){
	// @todo
};

Assert.equalsXmlStructure = function(expected, actual){
	// @todo
};

Assert.exists = function(cssSelector){
	if (document.body.querySelector(cssSelector) === null) {
		throw ('Selector "' + cssSelector + '" does not match');
	} else {
		this.addSuccess();
	}
};

Assert.func = function(actual){
	if (typeof actual !== 'function') {
		throw ('Is not a function');
	} else {
		this.addSuccess();
	}
};

Assert.instanceOf = function(classType, actual){
	if (actual instanceof classType) {
		throw ('Object is not of type "' + classType + '"');
	} else {
		this.addSuccess();
	}
};

Assert.object = function(actual){
	if (typeof actual !== 'object') {
		throw ('Is not an object');
	} else {
		this.addSuccess();
	}
};

Assert.objectHasProperty = function(object, property){
	if (typeof object[property] === 'undefined') {
		throw ('Object has no property "' + property + '"');
	} else {
		this.addSuccess();
	}
};