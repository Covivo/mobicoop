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
        >
          <v-img
            :width="size"
            :src="path+item.name"
            :alt="item.alt"
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
    size: {
      type: Number,
      default: null
    },
  },  
}

</script>