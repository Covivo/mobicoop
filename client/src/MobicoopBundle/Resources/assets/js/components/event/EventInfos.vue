<template>
  <v-row
    align="center"
    justify="center"
  >
    <v-col cols="4">
      <v-avatar
        color="grey lighten-3"
        size="225"
      >
        <!--        <img-->
        <!--          v-if="event.images[0]"-->
        <!--          :src="event['images'][0]['versions'][avatarVersion]"-->
        <!--          alt="avatar"-->
        <!--        >-->
        <!--        <img-->
        <!--          v-else-->
        <!--          :src="urlAltAvatar"-->
        <!--          alt="avatar"-->
        <!--        >-->
      </v-avatar>
    </v-col>
      
    <v-col
      cols="8"
    >
      <v-card
        flat
        height="25vh"
      >
        <v-card-text>
          <h3 class="headline">
            {{ event.name }}
          </h3>
          <p class="body-1">
            {{ event.description }}
          </p>
          <p class="body-2">
            {{ event.fullDescription }}
          </p>
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>
<script>
export default {
  props:{
    event: {
      type: Object,
      default: null
    },
    urlAltAvatar: {
      type: String,
      default: null
    },
    avatarVersion: {
      type: String,
      default: null
    }
  }
}
</script>