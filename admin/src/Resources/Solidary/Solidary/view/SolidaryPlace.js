import React from 'react';

const SolidaryPlace = ({ place }) => {
  if (place && place.displayLabel && place.displayLabel.length) {
    return place.displayLabel.map((l, i) => (
      <>
        {i === 0 ? <b>{l}</b> : l}
        <br />
      </>
    ));
  }
  return '?';
};

export default SolidaryPlace;
