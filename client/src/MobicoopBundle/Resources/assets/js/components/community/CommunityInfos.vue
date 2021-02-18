<template>
  <v-row align="center">
    <v-col
      cols="4"
      md="4"
      justify="center"
      align="center"
    >
      <v-img
        v-if="community.images[0]"
        :src="community['images'][0]['versions']['square_250']"
        width="225"
        height="200"
        :aspect-ratio="1"
        :alt="community.name"
        contain
      />
      <v-img
        v-else
        :src="urlAltAvatar"
        width="225"
        height="200"
        :aspect-ratio="1"
        :alt="community.name"
        contain
      />
    </v-col>

    <v-col
      cols="8"
      md="8"
    >
      <v-card
        flat
        justify="center"
      >
        <v-card-text>
          <h3 class="text-h5 text-justify font-weight-bold">
            {{ community.name }}
          </h3>
          <p
            class="text-body-1 text-justify"
            v-html="community.description"
          />
          <p
            class="text-body-2 text-justify"
            v-html="community.fullDescription"
          />
        </v-card-text>
      </v-card>
    </v-col>
  </v-row>
</template>
<script>
export default {
  props: {
    community: {
      type: Object,
      default: null,
    },
    urlAltAvatar: {
      type: String,
      default: null,
    },
    avatarVersion: {
      type: String,
      default: null,
    }
  },
};
</script>
