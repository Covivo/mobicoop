<template>
  <v-row
    align="center"
    justify="center"
  >
    <v-col cols="2">
      <v-avatar
        color="grey lighten-3"
        size="225"
      >
        <img
          v-if="community.images"
          :src="community.images[0].versions.square_800"
          alt="avatar"
        >
        <img
          v-else
          src="/images/avatar.svg"
          alt="avatar"
        >
      </v-avatar>
    </v-col>
      
    <v-col
      cols="4"
    >
      <v-card
        flat
        height="25vh"
      >
        <v-card-text>
          <h3 class="headline">
            {{ community.name }}
          </h3>
          <p class="body-1">
            {{ community.description }}
          </p>
          <p class="body-2">
            {{ community.fullDescription }}
          </p>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>
<script>
export default {
  props:{
    community: {
      type: Object,
      default: null
    }
  },
}
</script>