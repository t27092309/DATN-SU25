<script>
import axios from "axios";
import slugify from "slugify";

export default {
  name: "CategoryManager",
  data() {
    return {
      category: {
        name: "",
        slug: "",
        description: "",
      },
      categories: [],
      message: "",
      messageClass: "",
    };
  },
  watch: {
    "category.name"(newName) {
      this.category.slug = slugify(newName, {
        lower: true,
        strict: true,
        locale: "vi",
      });
    },
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    async fetchCategories() {
      try {
        const response = await axios.get(
          "http://your-backend-url/api/categories"
        );
        this.categories = response.data.data || response.data;
      } catch (error) {
        this.message = "Có lỗi khi tải danh sách danh mục!";
        this.messageClass = "text-danger";
      }
    },
    async addCategory() {
      try {
        const response = await axios.post(
          "http://your-backend-url/api/categories",
          {
            name: this.category.name,
            slug: this.category.slug,
            description: this.category.description,
          }
        );
        this.categories.push(response.data.data); // Thêm danh mục mới vào danh sách
        this.message = "Danh mục đã được thêm thành công!";
        this.messageClass = "text-success";
        this.category.name = "";
        this.category.slug = "";
        this.category.description = "";
      } catch (error) {
        this.message =
          error.response?.data?.message || "Có lỗi xảy ra khi thêm danh mục!";
        this.messageClass = "text-danger";
      }
    },
    editCategory(id) {
      this.$router.push(`/categories/edit/${id}`);
    },
    async deleteCategory(id) {
      if (confirm("Bạn có chắc muốn xóa danh mục này?")) {
        try {
          await axios.delete(`http://your-backend-url/api/categories/${id}`);
          this.categories = this.categories.filter(
            (category) => category.id !== id
          );
          this.message = "Danh mục đã được xóa!";
          this.messageClass = "text-success";
        } catch (error) {
          this.message =
            error.response?.data?.message || "Có lỗi khi xóa danh mục!";
          this.messageClass = "text-danger";
        }
      }
    },
  },
};
</script>

<template>
  <div class="container">
    <div class="page-inner">
      <div class="page-header">
        <h3 class="fw-bold mb-3">Các danh mục sản phẩm</h3>
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
                  <form @submit.prevent="addCategory" class="mb-5">
                    <div class="form-group">
                      <h5 class="card-title">Thêm mới danh mục sản phẩm</h5>
                    </div>
                    <div class="form-group">
                      <label for="name">Tên</label>
                      <input
                        type="text"
                        v-model="category.name"
                        class="form-control"
                        id="name"
                        placeholder="Nhập tên danh mục"
                        required
                      />
                      <small id="emailHelp2" class="form-text text-muted"
                        >Tên thuộc tính (được hiển thị ngoài frontend)</small
                      >
                    </div>
                    <div class="form-group">
                      <label for="duong-dan-tinh">Đường dẫn tĩnh</label>
                      <input
                        type="text"
                        v-model="category.slug"
                        class="form-control"
                        id="duong-dan-tinh"
                        placeholder="Nhập đường dẫn tĩnh"
                      />
                    </div>
                    <div class="form-group">
                      <label for="comment">Mô tả</label>
                      <textarea
                        v-model="category.description"
                        class="form-control"
                        id="comment"
                        rows="5"
                        placeholder="Nhập mô tả danh mục"
                      ></textarea>
                    </div>
                    <div class="card-action">
                      <button type="submit" class="btn btn-primary">
                        Thêm danh mục
                      </button>
                    </div>
                    <p v-if="message" :class="messageClass">{{ message }}</p>
                  </form>
                </div>

                <!-- Bảng hiện thị thuộc tính -->
                <div class="col-md-6 col-lg-8">
                  <div class="table-responsive">
                    <table id="add-row" class="display table table-bordered">
                      <thead>
                        <tr>
                          <th>Tên</th>
                          <th>Đường dẫn tĩnh</th>
                          <th>Mô tả</th>
                          <th style="width: 10%">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="category in categories" :key="category.id">
                          <td>{{ category.name }}</td>
                          <td>{{ category.slug }}</td>
                          <td>
                            {{ category.description || "Không có mô tả" }}
                          </td>
                          <td>
                            <div class="form-button-action">
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Edit Task"
                                class="btn btn-link btn-primary btn-lg"
                                @click="editCategory(category.id)"
                              >
                                <i class="fa fa-edit"></i>
                              </button>
                              <button
                                type="button"
                                data-bs-toggle="tooltip"
                                title="Remove"
                                class="btn btn-link btn-danger"
                                @click="deleteCategory(category.id)"
                              >
                                <i class="fa fa-times"></i>
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                    <p v-if="message" :class="messageClass">{{ message }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
