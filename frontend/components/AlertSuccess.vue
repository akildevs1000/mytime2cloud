<template>
   
		<div>
			 {{ response.msg }}
		</div>
</template>

<script>
export default {
    props: ["response", "alert_color"],
};
</script>