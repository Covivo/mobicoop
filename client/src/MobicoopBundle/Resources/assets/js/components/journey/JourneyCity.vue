<template>
  <v-container>
    <v-row
      align="center"
    >
      <!-- TITLE -->
      <v-col
        align="center"
        cols="12"
      >
        <h1>
          De ville à ville
        </h1>
      </v-col> 
    </v-row>
    <v-row>
      <v-col
        cols="12"
      >
        <v-tabs
          v-model="tab"
        >
          <v-tab
            v-for="item in items"
            :key="item.tab"
          >
            {{ item.tab }}
          </v-tab>
        </v-tabs>
        <v-tabs-items 
          v-model="tab"
        >
          <v-card
            class="mx-auto"
          >
            <v-list dense>
              <v-tab-item
                v-for="item in items"
                :key="item.tab"
              >
                <v-list-item
                  v-for="city in item.cities"
                  :key="city"
                >
                  <v-list-item-content>
                    <v-list-item-title>{{ city }}</v-list-item-title>
                  </v-list-item-content>
                </v-list-item>
              </v-tab-item>
            </v-list>
          </v-card>
          </v-tab-item>
        </v-tabs-items>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import axios from 'axios';

export default {
  data () {
    return {
      tab: 0,
      items: [
        { tab: 'A', cities: [] },
        { tab: 'B', cities: [] },
        { tab: 'C', cities: [] },
        { tab: 'D', cities: [] },
        { tab: 'E', cities: [] },
        { tab: 'F', cities: [] },
        { tab: 'G', cities: [] },
        { tab: 'H', cities: [] },
        { tab: 'I', cities: [] },
        { tab: 'J', cities: [] },
        { tab: 'K', cities: [] },
        { tab: 'L', cities: [] },
        { tab: 'M', cities: [] },
        { tab: 'N', cities: [] },
        { tab: 'O', cities: [] },
        { tab: 'P', cities: [] },
        { tab: 'Q', cities: [] },
        { tab: 'R', cities: [] },
        { tab: 'S', cities: [] },
        { tab: 'T', cities: [] },
        { tab: 'U', cities: [] },
        { tab: 'V', cities: [] },
        { tab: 'W', cities: [] },
        { tab: 'X', cities: [] },
        { tab: 'Y', cities: [] },
        { tab: 'Z', cities: [] }
      ]
    };
  },
  watch: {
    tab: function(newVal, oldVal){
      this.getCities(this.items[newVal].tab);
    }
  },
  mounted() {
    this.getCities(this.items[this.tab].tab);
  },
  methods: {
    getCities(letter) {
      console.log(letter);
      let item = this.items.find( item => item.tab == letter );
      if (item.cities.length == 0) {
        axios
          .get(`http://localhost:8080/journeys/cities`, {
            headers: { Authorization: 'Bearer ' + this.$root.token, Accept: 'application/json', 'Content-Type': 'application/json' },
            params: { letter: letter }
          })
          .then(res => {
            item.cities = res.data;
          });
      } 
    }
  }
};
</script>