@props(['designer'])

{{-- All portfolio modals --}}
<x-portfolio.modal.delete />
<x-portfolio.modal.edit-bio :designer="$designer" />
<x-portfolio.modal.edit-skills :designer="$designer" />
<x-portfolio.modal.edit-project />
<x-portfolio.modal.edit-product />
<x-portfolio.modal.edit-service />
<x-portfolio.modal.add-project />
<x-portfolio.modal.add-product />
<x-portfolio.modal.add-service />
