import React from 'react';
import { render } from '@testing-library/react';

import { AddressField } from './AddressField';

describe('AddressField', () => {
  it("should render a dash if address field doesn't exist", () => {
    const { getByText } = render(<AddressField record={{}} source="any" />);
    expect(getByText('-')).toBeInTheDocument();
  });

  it('should render address addressLocality if address field exists', () => {
    const { getByText } = render(
      <AddressField
        record={{ myAddress: { addressLocality: 'Dombasle-Sur-Meurthe' } }}
        source="myAddress"
      />
    );
    expect(getByText('Dombasle-Sur-Meurthe')).toBeInTheDocument();
  });
});
