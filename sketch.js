// Teachable Machine
// The Coding Train / Daniel Shiffman
// https://thecodingtrain.com/TeachableMachine/1-teachable-machine.html
// https://editor.p5js.org/codingtrain/sketches/PoZXqbu4v
// ===== SETTINGS =====
let imageModelURL = 'https://teachablemachine.withgoogle.com/models/bXy2kDNi/';

// ===== VARIABLES =====
let classifier;
let video;
let flippedVideo;
let label = "Loading...";
let redirected = false;
let confirmFrames = 0;

// ===== LOAD MODEL =====
function preload() {
  classifier = ml5.imageClassifier(imageModelURL + 'model.json');
}

// ===== SETUP =====
function setup() {
  createCanvas(340, 280);

  video = createCapture(VIDEO);
  video.size(340, 260);
  video.hide();

  classifyVideo();
}

// ===== DRAW =====
function draw() {
  background(0);

  // video
  image(flippedVideo, 0, 0);

  // overlay text
  fill(255);
  textSize(16);
  textAlign(CENTER);
  text(label, width / 2, height - 10);
}

// ===== CLASSIFY =====
function classifyVideo() {
  flippedVideo = ml5.flipImage(video);
  classifier.classify(flippedVideo, gotResult);
}

// ===== RESULT =====
function gotResult(error, results) {
  if (error) {
    console.error(error);
    return;
  }

  let predictedLabel = results[0].label;
  let confidence = results[0].confidence;

  // красиво выводим %
  let percent = (confidence * 100).toFixed(1);
  label = predictedLabel + " | " + percent + "%";

  // ===== LOGIC =====
  if (!redirected) {

    // ✅ если это ты и уверенность высокая
    if (predictedLabel === "Polina" && confidence > 0.95) {

      confirmFrames++;

      if (confirmFrames > 15) { // ~0.5 секунды подтверждение
        redirected = true;
        label = "Access Granted";

        setTimeout(() => {
          window.location.href = "admin.php";
        }, 800);
      }

    } else if (confidence > 0.95) {
      // ❌ если кто-то другой
      redirected = true;
      label = "Access Denied";

      setTimeout(() => {
        window.location.href = "registration.php";
      }, 800);
    } else {
      // если неуверенно — сбрасываем
      confirmFrames = 0;
    }
  }

  classifyVideo();
}
