<template>
  <v-card flat>
    <div class="d-flex flex-no-wrap justify-space-between">
      <div>
        <v-row align="center">
          <v-col>
            <v-avatar
              class="ma-0"
              size="125"
              tile
            >
              <a
                :href="link"
                :title="textLink"
              >
                <v-img :src="image" />
              </a>
            </v-avatar>
          </v-col>
          <v-col>
            <a
              :href="link"
              :title="textLink"
              style="text-decoration:none;"
            >{{ textLink }}</a>
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
