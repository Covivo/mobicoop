<template>
  <l-marker
    :lat-lng="point.latLng"
    :draggable="markersDraggable"
    @update:latLng="updateLatLng"
    @click="clickOnPoint(point.address)"
  >
    <l-icon
      v-if="point.icon.url!==undefined"
      :icon-size="point.icon.size"
      :icon-anchor="point.icon.anchor"
      :icon-url="point.icon.url"
    />
    <l-tooltip
      v-if="point.title!==''"
    >
      <p
        class="font-weight-bold"
        v-html="point.title"
      />
      <p
        v-if="point.popup"
        id="description-tooltip"
        v-html="point.popup.description"
      />
      <MMapRelayPointDescription
        v-if="relayPoints && point.misc"
        :data="point.misc"
      />
    </l-tooltip>

    <l-popup v-if="point.popup">
      <h3 v-html="point.popup.title" />
      <img
        v-if="point.popup.images && point.popup.images[0]"
        :src="point.popup.images[0]['versions']['square_100']"
        alt="avatar"
      >
      <p v-html="point.popup.description" />
      <p v-if="point.popup.date_begin && point.popup.date_end">
        {{ point.popup.date_begin }}<br> {{ point.popup.date_end }}
      </p>
    </l-popup>
  </l-marker>
</template>
<script>
import MMapRelayPointDescription from "@components/utilities/MMap/MMapRelayPointDescription"
export default {
  components: {
    MMapRelayPointDescription
  },
  props: {
    point: {
      type: Object,
      default: function(){return [];}
    },
    relayPoints: {
      type: Boolean,
      default: false
    },
    markersDraggable: {
      type: Boolean,
      default: false
    },
  },
  methods:{
    updateLatLng(data){
      this.$emit("updateLatLng",data);
    },
    clickOnPoint(point){
      this.$emit("clickOnPoint",point);
    }
  }
}
</script>