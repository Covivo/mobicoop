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
            width="50"
            :src="item.src"
            :alt="item.alt"
          />
        </a>
      </v-col>
    </v-row>
  </v-container>
</template>
<script>

import Logos from "@componentsData/base/MFooter/Logos.json"

export default {

  data() {
    return {
      items: []
    };
  },
  mounted() {
    this.getLogos();
  },
  methods:{
    getLogos() {
      for (let Logo in Logos)
      {
        this.items.push(Logos[Logo]);
      }
    }
  }
}

</script>