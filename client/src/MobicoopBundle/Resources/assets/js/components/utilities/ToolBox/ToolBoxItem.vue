<template>
  <v-card>
    <div class="d-flex flex-no-wrap justify-space-between">
      <div>
        <v-row>
          <v-col>
            <v-avatar
              class="ma-3"
              size="125"
              tile
            >
              <v-img :src="image" />
            </v-avatar>
          </v-col>
          <v-col>
            <v-card-subtitle>
              <a
                :href="link"
                :title="textLink"
              >{{ textLink }}</a>
            </v-card-subtitle>
          </v-col>
        </v-row>
      </div>
    </div>
  </v-card>
</template>

<script>
export default {
  props: {
    image:{
      type: String,
      default: null
    },
    link:{
      type: String,
      default: null
    },
    textLink:{
      type: String,
      default: null
    }
  },
  data() {
    return {
    }
  }
};
</script>
