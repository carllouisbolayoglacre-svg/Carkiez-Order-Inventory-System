<script>
document.addEventListener('DOMContentLoaded', () => {
  const quantities = document.querySelectorAll('.quantity');

  quantities.forEach(qty => {
    const minus = qty.querySelector('.minus');
    const plus = qty.querySelector('.plus');
    const input = qty.querySelector('input[type="number"]');

    minus.addEventListener('click', () => {
      const current = parseInt(input.value) || 0;
      const min = parseInt(input.min) || 1;
      if (current > min) input.value = current - 1;
    });

    plus.addEventListener('click', () => {
      const current = parseInt(input.value) || 0;
      const max = parseInt(input.max) || Infinity;
      if (current < max) input.value = current + 1;
    });
  });
});
</script>