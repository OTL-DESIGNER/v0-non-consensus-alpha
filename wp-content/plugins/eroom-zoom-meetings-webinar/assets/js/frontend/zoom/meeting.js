var meetingConfig = {
	sdkKey: API_KEY,
	secretKey: SECRET_KEY,
	meetingNumber: meeting_id,
	userName: username,
	passWord: meeting_password,
	leaveUrl: leaveUrl,
	role: 0, //0-Attendee,1-Host,5-Assistant
	userEmail: email,
	lang: lang,
	signature: "",
	china: 0,//0-GLOBAL, 1-China
};
meetingConfig.signature = ZoomMtg.generateSDKSignature({
	meetingNumber: meetingConfig.meetingNumber,
	sdkKey: meetingConfig.sdkKey,
	sdkSecret: meetingConfig.secretKey,
	role: meetingConfig.role,
	success: function (res) {
		console.log(res);
	},
});
console.log(JSON.stringify(ZoomMtg.checkSystemRequirements()));

// it's option if you want to change the MeetingSDK-Web dependency link resources. setZoomJSLib must be run at first
// ZoomMtg.setZoomJSLib("https://source.zoom.us/{VERSION}/lib", "/av"); // default, don't need call it
if (meetingConfig.china)
	ZoomMtg.setZoomJSLib("https://jssdk.zoomus.cn/3.10.0/lib", "/av"); // china cdn option

ZoomMtg.preLoadWasm();
ZoomMtg.prepareWebSDK();

function beginJoin(signature) {
	ZoomMtg.i18n.load(meetingConfig.lang);
	ZoomMtg.init({
		leaveUrl: meetingConfig.leaveUrl,
		disableCORP: !window.crossOriginIsolated, // default true
		// disablePreview: false, // default false
		externalLinkPage: meetingConfig.webEndpoint,
		success: function () {
			console.log(meetingConfig);
			console.log("signature", signature);
			ZoomMtg.join({
				meetingNumber: meetingConfig.meetingNumber,
				userName: meetingConfig.userName,
				signature: signature,
				sdkKey: meetingConfig.sdkKey,
				userEmail: meetingConfig.userEmail,
				passWord: meetingConfig.passWord,
				success: function (res) {
					console.log(username);
					console.log("join meeting success");
					console.log("get attendeelist");
					ZoomMtg.getAttendeeslist({});
					ZoomMtg.getCurrentUser({
					success: function (res) {
						console.log("success getCurrentUser", res.result.currentUser);
					},
					});
				},
				error: function (res) {
					console.log(res);
				},
			});
		},
		error: function (res) {
			console.log(res);
		},
	});
	
	ZoomMtg.inMeetingServiceListener("onUserJoin", function (data) {
		console.log("inMeetingServiceListener onUserJoin", data);
	});
	
	ZoomMtg.inMeetingServiceListener("onUserLeave", function (data) {
		console.log("inMeetingServiceListener onUserLeave", data);
	});
	
	ZoomMtg.inMeetingServiceListener("onUserIsInWaitingRoom", function (data) {
		console.log("inMeetingServiceListener onUserIsInWaitingRoom", data);
	});
	
	ZoomMtg.inMeetingServiceListener("onMeetingStatus", function (data) {
		console.log("inMeetingServiceListener onMeetingStatus", data);
	});
	
}

beginJoin(meetingConfig.signature);

