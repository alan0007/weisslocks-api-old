var firebase = require("firebase-admin");

var serviceAccount = require("serviceAccountKey.json");

firebase.initializeApp({
  credential: firebase.credential.cert(serviceAccount),
  databaseURL: "https://weiocks-7e0ce.firebaseio.com/"
});

var db = firebase.database();
var ref = db.ref("firebase");
ref.once("value", function(snapshot) {
  console.log(snapshot.val());
});

var usersRef = ref.child("token");
usersRef.set({
  alanisawesome: {
    testvisitor: "fsvU3oLITfw:APA91bG1H61UOCmsO5A2vNOS_8l-SjH41FDj8YHysmZ71_OjMBKLKeP4y08KhZbb_68WtlpAUWcpW1PygsNE1tUHoxs25QyLTuSmrTnS3y3HDpWVTCqQJ29xSmcbzLXLRUPwTKIJtRzh"
  }
});