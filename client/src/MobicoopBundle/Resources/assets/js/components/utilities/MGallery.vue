<template>
  <v-container fluid>
    <v-row
      align-content-sm
      justify="center"
      justify-sm
    >
      <v-col
        v-for="item in items"
        :key="item.index"
        class="d-flex child-flex"
        cols="1"
        align-self="center"
      >
        <a
          :href="item.link"
          :aria-label="item.arialabel"
        >
          <v-img
            :max-height="maxHeight"
            :src="path+item.name"
            :alt="item.alt"
            :width="item.width"
            :height="item.height"
            contain
          />
        </a>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

export default {
  props: {
    path: {
      type: String,
      default: null
    },
    items: {
      type: Object,
      default: null
    },
    maxHeight: {
      type: Number,
      default: null
    },
  },
}

</script>
