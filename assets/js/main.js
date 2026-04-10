const filter = document.getElementById("filter");
const games = document.querySelectorAll(".game-card");

filter.addEventListener("change", () => {
  const value = filter.value;

  games.forEach(game => {
    if (value === "all" || game.dataset.category === value) {
      game.style.display = "block";
    } else {
      game.style.display = "none";
    }
  });
});