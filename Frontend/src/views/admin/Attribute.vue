<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Add new attribute</h3>
        <ul class="breadcrumbs mb-3">
          <li class="nav-home">
            <a href="#">
              <i class="icon-home"></i>
            </a>
          </li>
          <li class="separator">
            <i class="icon-arrow-right"></i>
          </li>

        </ul>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <div class="card-title">Form Elements</div>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 col-lg-4">
                  <div class="form-group">
                    <label for="email2">Tên</label>
                    <input type="text" class="form-control" id="email2" />
                    <small id="emailHelp2" class="form-text text-muted"
                      >Tên thuộc tính(được hiển thị ngoài frontend)</small
                    >
                  </div>
                  <div class="form-group">
                    <label for="password">Đường dẫn tĩnh</label>
                    <input type="text" class="form-control" id="password" />
                  </div>
                  <div class="form-group">
                    <label for="exampleFormControlSelect1">Loại</label>
                    <select class="form-select" id="exampleFormControlSelect1">
                      <option>Lựa chọn</option>
                      <option>2</option>
                      <option>3</option>
                      <option>4</option>
                      <option>5</option>
                    </select>
                    <small id="emailHelp2" class="form-text text-muted"
                      >Xác định cách bạn chọn thuộc tính cho sản phẩm. Bên dưới
                      bảng điều khiển quản trị -> sản phẩm -> dữ liệu sản phẩm
                      -> thuộc tính -> giá trị, Văn bản cho phép tự nhập văn bản
                      trong khi chọn các tên giới hạn cấu hình sẵn trong một
                      danh sách dạng xổ xuống.</small
                    >
                  </div>
                  <div class="form-group">
                    <label for="exampleFormControlSelect1">Loại</label>
                    <select class="form-select" id="exampleFormControlSelect1">
                      <option>Sắp xếp mặc định</option>
                      <option>2</option>
                      <option>3</option>
                      <option>4</option>
                      <option>5</option>
                    </select>
                    <small id="emailHelp2" class="form-text text-muted"
                      >Xác định thứ tự sắp xếp của tên các chủng loại hàng hóa
                      trên trang sản phẩm của trang web. Nếu sử dụng sắp xếp tùy
                      chỉnh, bạn có thể kéo và thả các tên của các chủng loại
                      hàng hóa trong thuộc tính này.</small
                    >
                  </div>

                  <div class="form-check">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      value=""
                      id="flexCheckDefault"
                    />
                    <label class="form-check-label" for="flexCheckDefault">
                      Agree with terms and conditions
                    </label>
                  </div>
                </div>

                <!-- Bảng hiện thị thuộc tính -->
                <div class="col-md-6 col-lg-8">
                  <div class="table-responsive">
                    <table
                      id="add-row"
                      class="display table "
                    >
                      <thead>
                        <tr>
                          <th>Tên</th>
                          <th>Đường dẫn tĩnh</th>
                          <th>Loại</th>
                          <th>Sắp xếp theo</th>
                          <th>Tên chủng loại</th>
                          <th style="width: 10%">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Tiger Nixon</td>
                          <td>System Architect</td>
                          <td>Edinburgh</td>
                          <td>Edinburgh</td>
                          <td>Edinburgh</td>
                          <td>
                            <div class="form-button-action">
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title=""
                                class="btn btn-link btn-primary btn-lg"
                                data-original-title="Edit Task"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title=""
                                class="btn btn-link btn-danger"
                                data-original-title="Remove"
                              >
                                <i class="fa fa-times"></i>
                              </button>
                            </div>
                          </td>
                        </tr>

                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
            <div class="card-action">
              <button class="btn btn-primary">Add attribute</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
