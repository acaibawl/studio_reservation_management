<script setup lang="ts">
// ドメインのindexにアクセスされたら会員向けの予約空き状況ページにリダイレクトする
const date = new Date();
navigateTo(`/reservations/availability/date/${date.toLocaleDateString('sv-SE')}`);
</script>
