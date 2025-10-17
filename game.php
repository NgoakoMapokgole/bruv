<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Snake Game</title>
  <link rel="stylesheet" href="../mainStyle.css">
  <link rel="icon" type="image/svg+xml" href="http://cs3-dev.ict.ru.ac.za/practicals/4a2/logo.png">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      background: black;
      color: white;
      overflow-x: hidden; /* prevent horizontal scroll */
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 10;
    }

    main {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100%;
      padding-top: 80px; /* space for fixed nav */
      box-sizing: border-box;
      position: relative;
    }

    h1.game-title {
      margin: 10px 0;
      font-size: 32px;
      text-align: center;
    }

    canvas {
      border: 1px solid white;
      margin-top: 10px;
    }

    #gameOver {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.85);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      display: none;
      z-index: 5;
    }

    #gameOver button {
      margin-top: 20px;
      padding: 10px 20px;
      font-size: 18px;
      cursor: pointer;
      border: none;
      border-radius: 6px;
      background: #00e054;
      color: black;
    }

    footer {
      text-align: center;
      padding: 10px;
      background: #111;
      position: sticky;
      bottom: 0;
      visibility: hidden;
    }

    body.show-footer footer {
      visibility: visible;
    }
  </style>
</head>
<body>
  <header>
    <?php include "../nav.php"; ?>
  </header>

  <main>
    <h1 class="game-title">SNAKE GAME</h1>
    <canvas id="game" width="400" height="400"></canvas>

    <div id="gameOver">
      <div>Game Over!</div>
      <div id="finalScore"></div>
      <button id="restartBtn">Restart</button>
    </div>
  </main>


    <?php include "../foot.php"; ?>


  <script>
    // Show footer only when scrolling
    window.addEventListener('scroll', () => {
      document.body.classList.add('show-footer');
    });

    const canvas = document.getElementById('game');
    const context = canvas.getContext('2d');
    const grid = 16;
    let count = 0;
    let snake, apple, score, highScore;

    function initGame() {
      snake = { x: 160, y: 160, dx: grid, dy: 0, cells: [], maxCells: 4 };
      apple = { x: 320, y: 320 };
      score = 0;
      highScore = localStorage.getItem('highScore') || 0;
      document.getElementById('gameOver').style.display = 'none';
    }

    function getRandomInt(min, max) {
      return Math.floor(Math.random() * (max - min)) + min;
    }

    function loop() {
      requestAnimationFrame(loop);
      if (++count < 4) return;
      count = 0;
      context.clearRect(0, 0, canvas.width, canvas.height);

      // Move snake
      snake.x += snake.dx;
      snake.y += snake.dy;

      // Wrap edges
      if (snake.x < 0) snake.x = canvas.width - grid;
      else if (snake.x >= canvas.width) snake.x = 0;
      if (snake.y < 0) snake.y = canvas.height - grid;
      else if (snake.y >= canvas.height) snake.y = 0;

      snake.cells.unshift({x: snake.x, y: snake.y});
      if (snake.cells.length > snake.maxCells) snake.cells.pop();

      // Draw apple
      context.fillStyle = 'red';
      context.fillRect(apple.x, apple.y, grid-1, grid-1);

      // Draw snake
      context.fillStyle = 'green';
      let gameOver = false;

      snake.cells.forEach((cell, index) => {
        context.fillRect(cell.x, cell.y, grid-1, grid-1);

        if (cell.x === apple.x && cell.y === apple.y) {
          snake.maxCells++;
          score++;
          if (score > highScore) {
            highScore = score;
            localStorage.setItem('highScore', highScore);
          }
          apple.x = getRandomInt(0, 25) * grid;
          apple.y = getRandomInt(0, 25) * grid;
        }

        for (let i = index + 1; i < snake.cells.length; i++) {
          if (cell.x === snake.cells[i].x && cell.y === snake.cells[i].y) {
            gameOver = true;
          }
        }
      });

      // Draw scores
      context.fillStyle = 'white';
      context.font = '16px Arial';
      context.fillText(`Score: ${score}`, 10, 20);
      context.fillText(`High Score: ${highScore}`, 10, 40);

      if (gameOver) {
        document.getElementById('finalScore').textContent = `Your Score: ${score}`;
        document.getElementById('gameOver').style.display = 'flex';
        return;
      }
    }

    // Prevent arrow keys from scrolling page
    document.addEventListener('keydown', e => {
      if ([37,38,39,40].includes(e.which)) e.preventDefault();
      if (e.which === 37 && snake.dx === 0) { snake.dx = -grid; snake.dy = 0; }
      else if (e.which === 38 && snake.dy === 0) { snake.dy = -grid; snake.dx = 0; }
      else if (e.which === 39 && snake.dx === 0) { snake.dx = grid; snake.dy = 0; }
      else if (e.which === 40 && snake.dy === 0) { snake.dy = grid; snake.dx = 0; }
    });

    document.getElementById('restartBtn').addEventListener('click', () => {
      initGame();
    });

    initGame();
    requestAnimationFrame(loop);
  </script>
</body>
</html>
