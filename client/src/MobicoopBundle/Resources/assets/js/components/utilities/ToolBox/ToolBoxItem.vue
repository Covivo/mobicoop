<template>
  <v-card flat>
    <div>
      <v-row align="center">
        <v-col
          cols="6"
          md="8"
          lg="6"
          class="text-left"
        >
          <v-avatar
            class="ma-0"
            size="125"
            tile
          >
            <a
              :href="downloadLink"
              :title="textDownload"
            >
              <v-img
                :src="image"
                contain
                max-width="90"
              />
            </a>
          </v-avatar>
        </v-col>
        <v-col
          cols="6"
          md="8"
          lg="6"
          class="text-left"
        >
          <h4 class="primary--text text-left font-weight-bold">
            {{ title }}
          </h4>
          <a
            class="primary--text body-2"
            style="text-decoration:none; background-color: #ffff66;"
            :href="linkSubtitle"
            target="_blank"
          >
            {{ subtitle }}
          </a>
          <div v-if="textDownload">
            <a
              style="text-decoration:none;"
              class="primary--text body-2"
              :href="downloadLink"
              :title="textDownload"
              download
              target="_blank"
            >
              {{ textDownload }}
            </a>
            <br>
          </div>
          <div v-if="personalizedText">
            <a
              :href="personalizedTextLink"
              :title="personalizedText"
              style="text-decoration:none;"
              class="accent--text body-2"
              download
            >
              {{ personalizedText }}</a>
            <br>
          </div>
          <ul>
            <li v-if="textLogo1">
              <a
                :href="link1"
                :title="textLogo1"
                style="text-decoration:none;"
                class="body-2"
                :class="linkColor"
              >{{ textLogo1 }}</a>
            </li>
            <li v-if="textLogo2">
              <a
                :href="link2"
                :title="textLogo2"
                style="text-decoration:none;"
                class="body-2"
                :class="linkColor"
              >{{ textLogo2 }}</a>
            </li>
            <li v-if="textLogo3">
              <a
                :href="link3"
                :title="textLogo3"
                style="text-decoration:none;"
                class="body-2"
                :class="linkColor"
              >{{ textLogo3 }}</a>
            </li>
          </ul>
        </v-col>
      </v-row>
    </div>
  </v-card>
</template>

<script>
export default {
  props: {
    image: {
      type: String,
      default: null
    },
    textDownload: {
      type: String,
      default: null
    },
    downloadLink: {
      type: String,
      default: null
    },
    personalizedText: {
      type: String,
      default: null
    },
    personalizedTextLink: {
      type: String,
      default: null
    },
    linkColor: {
      type: String,
      default: 'accent--text'
    },
    link1: {
      type: String,
      default: null
    },
    link2: {
      type: String,
      default: null
    },
    link3: {
      type: String,
      default: null
    },
    textLogo1: {
      type: String,
      default: null
    },
    textLogo2: {
      type: String,
      default: null
    },
    textLogo3: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: null
    },
    subtitle: {
      type: String,
      default: null
    },
    linkSubtitle: {
      type: String,
      default: null
    }
  }
};
</script>
<style lang="scss" scoped>
ul {
  list-style-type: none;
  padding-left: 0;
}
</style>
