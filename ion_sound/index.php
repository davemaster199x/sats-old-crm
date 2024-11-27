<!DOCTYPE html>
<html>
<head>
<title>Title of the document</title>
<script src="ion.sound.min.js"></script>
</head>

<body>
The content of the document......
<script>
// init bunch of sounds
ion.sound({
    sounds: [
        {name: "beer_can_opening"},
        {name: "bell_ring"},
        {name: "branch_break"},
        {name: "button_click"}
    ],

    // main config
    path: "sounds/",
    preload: true,
    multiplay: true,
    volume: 0.9
});

// play sound
ion.sound.play("beer_can_opening");
</script>
</body>

</html>