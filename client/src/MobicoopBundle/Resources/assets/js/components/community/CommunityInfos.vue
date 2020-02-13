<template>
  <v-row
    align="center"
    justify="center"
  >
    <v-col
      cols="4"
      md="4"
      justify="center"
      align="center"
    >
      <v-avatar
        color="grey lighten-3"
        size="225"        
      >
        <img
          v-if="community.images[0]"
          :src="community['images'][0]['versions'][avatarVersion]"
          alt="avatar"
        >
        <img
          v-else
          :src="urlAltAvatar"
          alt="avatar"
        >
      </v-avatar>
    </v-col>
      
    <v-col
      cols="8"
      md="8"
    >
      <v-card
        flat
        height="25vh"
        justify="center"
      >
        <v-card-text>
          <h3 class="headline text-justify font-weight-bold">
            {{ community.name }}
          </h3>
          <p class="body-1 text-justify">
            {{ community.description }}
          </p>
          <p class="body-2 text-justify">
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