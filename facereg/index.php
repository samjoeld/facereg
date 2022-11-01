<html>
    
    <head>
        Face Reg - Dev Smithworks
        <style>
            html, body {
  height: 100%;
  width: 100%;
  font-family: -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, "PT Sans", "Helvetica Neue", Arial, sans-serif;
  background: black;
}

body {
  margin: 0;
  padding: 0;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

#model-loader {
  line-height: 100vh;
  z-index: 99;
  width: 50%;
  text-align: center;
  color: white;
  position: absolute;
  right: 0;
}

#webcam-loader {
  line-height: 100vh;
  width: 50%;
  text-align: center;
  color: white;
  position: absolute;
  left: 0;
}

#canvas,
#video {
  margin: 10px auto;
  background: black;
}

canvas {
  transform: scale(1.5);
}
        </style>
    </head>
    <body>
        <!-- Smile — you’re being watched -->
<video id="video" crossOrigin="anonymous" width="500" height="375" autoplay></video>
<div id="webcam-loader">Loading Webcam...</div>
<div id="model-loader">Loading Model...</div>


    </body>
    <script>
        const webcam = document.getElementById('video');
const modelLoader = document.getElementById('model-loader');
const webcamLoader = document.getElementById('webcam-loader');

const modelsDirectory = 'https://assets.codepen.io/1290466/';

window.onload = async () => {
  await Promise.all([
  faceapi.loadTinyFaceDetectorModel(modelsDirectory),
  faceapi.loadFaceLandmarkTinyModel(modelsDirectory),
  faceapi.loadFaceRecognitionModel(modelsDirectory),
  faceapi.loadFaceExpressionModel(modelsDirectory)]);


  navigator.getUserMedia(
  { video: {} },
  cameraStream => webcam.srcObject = cameraStream,
  error => console.error(error));


  webcam.addEventListener('play', () => {
    webcamLoader.style.display = 'none';
    const options = new faceapi.TinyFaceDetectorOptions();
    const useTinyModel = true;
    const canvas = faceapi.createCanvasFromMedia(webcam);
    const canvasSize = { width: webcam.width, height: webcam.height };

    document.body.append(canvas);
    faceapi.matchDimensions(canvas, canvasSize);

    setInterval(async () => {
      const detections = await faceapi.detectSingleFace(webcam, options).
      withFaceLandmarks(useTinyModel).
      withFaceExpressions();

      if (detections) {
        modelLoader.style.display = 'none';
        const resizedDetection = faceapi.resizeResults(detections, canvasSize);
        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
        faceapi.draw.drawFaceLandmarks(canvas, resizedDetection);

        if (resizedDetection) {
          const box = resizedDetection.detection.box;
          const { expressions } = resizedDetection;
          const text = `${Object.keys(expressions).filter((x) =>
          expressions[x] == Math.max.apply(null, Object.values(expressions)))
          }`;
          const textPosition = text.length * 5;
          const anchor = { x: canvas.width / 2 - 30 - textPosition, y: 60 };
          const drawOptions = {
            fontSize: 35,
            fontStyle: 'arial',
            fontColor: '#ea3bf7' };

          const drawBox = new faceapi.draw.DrawTextField(text, anchor, drawOptions);

          drawBox.draw(canvas);
        }
      }
    }, 100);
  });
};
    </script>
    <script src="face-api.min.js"></script>
</html>